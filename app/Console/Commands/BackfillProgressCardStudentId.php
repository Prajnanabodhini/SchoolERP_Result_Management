<?php

namespace App\Console\Commands;

use App\Models\StudentProgressCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillProgressCardStudentId extends Command
{
    protected $signature   = 'progress-cards:backfill-studentid {--dry-run : Preview only, do not save}';
    protected $description = 'Populate studentid in student_progress_cards from ERP by pen / gr_no / roll_no / name match';

    // saralid is bigint in FeeMstStudent — 19-digit PENs overflow int.
private const SQL_BIGINT_MIN = '-9223372036854775808';
private const SQL_BIGINT_MAX =  '9223372036854775807';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) $this->warn('DRY RUN — nothing will be written.');

        $rows = StudentProgressCard::query()
            ->where(function ($q) {
                $q->whereNull('studentid')->orWhere('studentid', '');
            })
            ->orderBy('id')
            ->get();

        $this->info("Rows needing backfill: {$rows->count()}");

        $matched = $skipped = $ambiguous = $errored = 0;

        foreach ($rows as $row) {
            try {
                [$studentid, $reason] = $this->resolveStudentId($row);
            } catch (\Throwable $e) {
                $this->error(sprintf('  [%d] pen=%s name=%s  =>  ERROR: %s',
                    $row->id, $row->pen ?: '-', $row->full_student_name ?: '-', $e->getMessage()));
                Log::warning('Backfill errored for row', ['id' => $row->id, 'error' => $e->getMessage()]);
                $errored++;
                continue;
            }

            if ($studentid !== null) {
                $this->line(sprintf('  [%d] pen=%s name=%s  =>  studentid=%s  (%s)',
                    $row->id, $row->pen ?: '-', $row->full_student_name ?: '-', $studentid, $reason));

                if (!$dryRun) {
                    try {
                        $row->studentid = (string) $studentid;
                        $row->save();
                    } catch (\Throwable $e) {
                        $this->error("     save failed: " . $e->getMessage());
                        $errored++;
                        continue;
                    }
                }
                $matched++;
            } elseif ($reason === 'ambiguous') {
                $this->warn(sprintf('  [%d] pen=%s name=%s  =>  AMBIGUOUS (skipped, resolve manually)',
                    $row->id, $row->pen ?: '-', $row->full_student_name ?: '-'));
                $ambiguous++;
            } else {
                $this->warn(sprintf('  [%d] pen=%s name=%s  =>  NO MATCH',
                    $row->id, $row->pen ?: '-', $row->full_student_name ?: '-'));
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Matched: {$matched}   Ambiguous: {$ambiguous}   Unmatched: {$skipped}   Errors: {$errored}");

        $this->reportDuplicates();

        return self::SUCCESS;
    }

    /** @return array{0:?string, 1:string} [studentid|null, reason] */
    private function resolveStudentId(StudentProgressCard $row): array
    {
        $erpYearId = $this->resolveErpYearId($row->academic_year_id);

        // ── 1) pen = Studentid ─────────────────────────────────────────────
        if (!empty($row->pen)) {
            try {
                if ($hit = DB::connection('sqlsrv_olderp')
                    ->table('dbo.FeeMstStudent')
                    ->where('Studentid', $row->pen)
                    ->first()) {
                    return [(string) $hit->Studentid, 'pen=Studentid'];
                }
            } catch (\Throwable $e) {
                Log::warning('pen=Studentid probe failed', [
                    'id' => $row->id, 'pen' => $row->pen, 'error' => $e->getMessage(),
                ]);
            }

            // ── 2) pen = saralid ────────────────────────────────────────────
            if ($this->fitsSqlBigInt($row->pen)) {
                try {
                    if ($hit = DB::connection('sqlsrv_olderp')
                        ->table('dbo.FeeMstStudent')
                        ->where('saralid', $row->pen)
                        ->first()) {
                        return [(string) $hit->Studentid, 'pen=saralid'];
                    }
                } catch (\Throwable $e) {
                    Log::warning('pen=saralid probe failed', [
                        'id' => $row->id, 'pen' => $row->pen, 'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // ── 3) gr_no → SubStudentMst.regno (stable per-student) ────────────
        if (!empty($row->gr_no) && is_numeric($row->gr_no)) {
            try {
                $hits = DB::connection('sqlsrv_olderp')
                    ->table('dbo.SubStudentMst')
                    ->where('regno', (int) $row->gr_no)
                    ->select('Studentid')
                    ->distinct()
                    ->get();

                if ($hits->count() === 1) {
                    return [(string) $hits->first()->Studentid, 'gr_no=regno'];
                }

                // multiple enrollments across years — narrow to target year
                if ($hits->count() > 1 && !empty($erpYearId)) {
                    $yearHits = DB::connection('sqlsrv_olderp')
                        ->table('dbo.SubStudentMst')
                        ->where('regno', (int) $row->gr_no)
                        ->where('yearid', $erpYearId)
                        ->select('Studentid')
                        ->distinct()
                        ->get();
                    if ($yearHits->count() === 1) {
                        return [(string) $yearHits->first()->Studentid, 'gr_no+year'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('gr_no probe failed', [
                    'id' => $row->id, 'gr_no' => $row->gr_no, 'error' => $e->getMessage(),
                ]);
            }
        }

        // ── 4) roll_no + yearid → SubStudentMst ───────────────────────────
        if (!empty($row->roll_no) && !empty($erpYearId)) {
            try {
                $hits = DB::connection('sqlsrv_olderp')
                    ->table('dbo.SubStudentMst')
                    ->where('yearid', $erpYearId)
                    ->where('rollno', (string) $row->roll_no)
                    ->select('Studentid')
                    ->distinct()
                    ->get();
                if ($hits->count() === 1) {
                    return [(string) $hits->first()->Studentid, 'rollno+year'];
                }
            } catch (\Throwable $e) {
                Log::warning('rollno probe failed', [
                    'id' => $row->id, 'roll_no' => $row->roll_no, 'error' => $e->getMessage(),
                ]);
            }
        }

        // ── 5) unique-name in FeeMstStudent, refined by year enrollment ────
        if (!empty($row->full_student_name)) {
            $target = strtoupper(trim($row->full_student_name));
            try {
                $hits = DB::connection('sqlsrv_olderp')
                    ->table('dbo.FeeMstStudent')
                    ->whereRaw(
                        "UPPER(LTRIM(RTRIM(ISNULL(studname,'') + ' ' + ISNULL(fathername,'')))) = ?",
                        [$target]
                    )
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('name probe failed', [
                    'id' => $row->id, 'name' => $target, 'error' => $e->getMessage(),
                ]);
                $hits = collect();
            }

            if ($hits->count() === 1) {
                return [(string) $hits->first()->Studentid, 'unique-name'];
            }

            // 5a) multiple name hits — keep only those enrolled in target year
            if ($hits->count() > 1 && !empty($erpYearId)) {
                $candidateIds = $hits->pluck('Studentid')->filter()->unique()->values()->toArray();
                try {
                    $enrolledIds = DB::connection('sqlsrv_olderp')
                        ->table('dbo.SubStudentMst')
                        ->whereIn('Studentid', $candidateIds)
                        ->where('yearid', $erpYearId)
                        ->distinct()
                        ->pluck('Studentid')
                        ->toArray();
                    if (count($enrolledIds) === 1) {
                        return [(string) $enrolledIds[0], 'unique-name+enrolled-year'];
                    }
                } catch (\Throwable $e) {
                    Log::warning('enrollment filter failed', [
                        'id' => $row->id, 'name' => $target, 'error' => $e->getMessage(),
                    ]);
                }
            }

            // 5b) token-based name match (handles "SHRADDHA MANGALDAS CHETAGE"
            //     vs "CHETAGE SHRADDHA MANGALDAS") then refine by year enrollment
            if ($hits->count() > 1) {
                $targetTokens = $this->nameTokens($target);
                if (!empty($targetTokens)) {
                    try {
                        $candidateRows = DB::connection('sqlsrv_olderp')
                            ->table('dbo.FeeMstStudent')
                            ->select('Studentid', 'studname', 'fathername')
                            ->get();

                        $tokenMatches = [];
                        foreach ($candidateRows as $cand) {
                            $candTokens = $this->nameTokens(
                                strtoupper(trim((string) $cand->studname . ' ' . (string) $cand->fathername))
                            );
                            if (empty($candTokens)) continue;
                            $min = min(count($targetTokens), count($candTokens));
                            if ($min === 0) continue;
                            if (count(array_intersect($targetTokens, $candTokens)) === $min) {
                                $tokenMatches[] = $cand;
                            }
                        }

                        // filter token matches to target year enrollment
                        if (!empty($erpYearId)) {
                            $tokenIds = collect($tokenMatches)->pluck('Studentid')->filter()->unique()->values()->toArray();
                            if (!empty($tokenIds)) {
                                $enrolledIds = DB::connection('sqlsrv_olderp')
                                    ->table('dbo.SubStudentMst')
                                    ->whereIn('Studentid', $tokenIds)
                                    ->where('yearid', $erpYearId)
                                    ->distinct()
                                    ->pluck('Studentid')
                                    ->toArray();
                                if (count($enrolledIds) === 1) {
                                    return [(string) $enrolledIds[0], 'token-name+enrolled-year'];
                                }
                            }
                        }

                        if (count($tokenMatches) === 1) {
                            return [(string) $tokenMatches[0]->Studentid, 'unique-name-tokens'];
                        }
                    } catch (\Throwable $e) {
                        Log::warning('token-name probe failed', [
                            'id' => $row->id, 'name' => $target, 'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            if ($hits->count() > 1) return [null, 'ambiguous'];
        }

        return [null, 'no-match'];
    }

    /** Resolve academic_years.id → SubStudentMst.yearid (int). */
    private function resolveErpYearId($academicYearId): ?string
    {
        if (empty($academicYearId)) return null;
        try {
            $year = DB::table('academic_years')->where('id', $academicYearId)->first();
            if (!$year) return null;
            $erp = $year->old_year_id ?? substr((string) ($year->year_name ?? ''), 0, 4);
            return $erp !== '' ? (string) $erp : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fitsSqlBigInt($value): bool
{
    if ($value === null || $value === '') return false;
    if (!is_numeric($value)) return false;
    // Compare as strings — PHP float loses precision near 2^63.
    $n = (string) $value;
    return bccomp($n, self::SQL_BIGINT_MIN, 0) >= 0
        && bccomp($n, self::SQL_BIGINT_MAX, 0) <= 0;
}

    private function nameTokens($name): array
    {
        $name = strtoupper(trim((string) $name));
        if ($name === '') return [];
        return array_values(array_filter(preg_split('/\s+/', $name) ?: [], fn ($t) => $t !== ''));
    }

    private function reportDuplicates(): void
    {
        $dupes = DB::table('student_progress_cards')
            ->select('studentid', 'exam_master_id', 'academic_year_id', DB::raw('COUNT(*) AS cnt'))
            ->whereNotNull('studentid')
            ->where('studentid', '<>', '')
            ->groupBy('studentid', 'exam_master_id', 'academic_year_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($dupes->isEmpty()) {
            $this->info('No duplicates detected.');
            return;
        }

        $this->newLine();
        $this->warn("Duplicate groups detected: {$dupes->count()}");
        foreach ($dupes as $d) {
            $this->line("  studentid={$d->studentid}  exam={$d->exam_master_id}  year={$d->academic_year_id}  count={$d->cnt}");
        }
        $this->warn('Resolve duplicates manually before adding the unique index.');
    }
}