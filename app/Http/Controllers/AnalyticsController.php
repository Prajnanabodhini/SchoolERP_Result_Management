<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentMark;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use App\Helpers\StudentHelper;
use App\Helpers\ExamStructureHelper;

class AnalyticsController extends Controller
{
    /* =====================================================================
     |  HELPERS
     | ===================================================================== */

    /**
     * SQL that resolves student_marks.subject_id → subjects.id.
     */
    private function resolvedSubjectIdSql(): string
    {
        return "
            COALESCE(
                (SELECT sws1.subject_id
                 FROM standard_wise_subjects sws1
                 WHERE sws1.standard_id = student_marks.standard_id
                   AND sws1.subject_id = student_marks.subject_id
                   AND sws1.is_active  = 1
                 LIMIT 1),

                (SELECT sws2.subject_id
                 FROM standard_wise_subjects sws2
                 WHERE sws2.standard_id = student_marks.standard_id
                   AND sws2.id         = student_marks.subject_id
                   AND sws2.is_active  = 1
                 LIMIT 1),

                student_marks.subject_id
            )
        ";
    }

    /**
     * Overwrite theory/oral/practical max + passing marks using
     * ExamStructureHelper — the same source the Exam Master edit page uses.
     */
    private function attachStructureMaxes($rows, int $standardId, string $examName)
    {
        return $rows->map(function ($r) use ($standardId, $examName) {

            $components = ExamStructureHelper::getComponentValues(
                $standardId,
                $examName,
                $r->subject_name ?? ''
            );

            /* ---------- THEORY ---------- */
            $r->theory_max = (float) ($components['theory_max_marks'] ?? 0);
            if ($r->theory_max <= 0) {
                $r->theory_max = (float) ($r->theory_max_marks ?? 0);
            }
            $r->theory_passing = (float) ($components['theory_passing_marks'] ?? 0);
            if ($r->theory_passing <= 0 && $r->theory_max > 0) {
                $r->theory_passing = (int) ceil($r->theory_max * 0.35);
            }
            $r->theory_obtained = (float) ($r->theory_obtained_marks ?? 0);

            /* ---------- ORAL ---------- */
            $r->oral_max = (float) ($components['oral_max_marks'] ?? 0);
            if ($r->oral_max <= 0) {
                $r->oral_max = (float) ($r->oral_max_marks ?? 0);
            }
            $r->oral_passing = (float) ($components['oral_passing_marks'] ?? 0);
            if ($r->oral_passing <= 0 && $r->oral_max > 0) {
                $r->oral_passing = (int) ceil($r->oral_max * 0.35);
            }
            $r->oral_obtained = (float) ($r->oral_obtained_marks ?? 0);

            /* ---------- PRACTICAL ---------- */
            $r->practical_max = (float) ($components['practical_max_marks'] ?? 0);
            if ($r->practical_max <= 0) {
                $r->practical_max = (float) ($r->practical_max_marks ?? 0);
            }
            $r->practical_passing = (float) ($components['practical_passing_marks'] ?? 0);
            if ($r->practical_passing <= 0 && $r->practical_max > 0) {
                $r->practical_passing = (int) ceil($r->practical_max * 0.35);
            }
            $r->practical_obtained = (float) ($r->practical_obtained_marks ?? 0);

            /* ---------- TOTALS ---------- */
            $r->obtained = $r->theory_obtained + $r->oral_obtained + $r->practical_obtained;
            $r->max      = $r->theory_max + $r->oral_max + $r->practical_max;
            $r->passing  = $r->theory_passing + $r->oral_passing + $r->practical_passing;
            $r->percent  = $r->max > 0 ? round(($r->obtained / $r->max) * 100, 2) : 0;

            return $r;
        });
    }

    /**
     * Collect subject ids that belong to a given exam.
     */
    private function allowedSubjectIds(?int $examId, int $standardId): array
    {
        if (!$examId) {
            return [];
        }

        return DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->where('standard_id', $standardId)
            ->pluck('subject_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }


    /* =====================================================================
     |  INDEX — dashboard
     | ===================================================================== */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $exams         = ExamMaster::orderBy('display_order')->get();
        $standards     = Standard::orderBy('display_order')->get();
        $divisions     = Division::orderBy('display_order')->get();

        $yearId     = $request->academic_year_id;
        $standardId = $request->standard_id;
        $divisionId = $request->division_id;
        $examId     = $request->exam_master_id;

        $hasFilters = $request->filled('academic_year_id')
                   && $request->filled('standard_id')
                   && $request->filled('division_id');

        if (!$hasFilters) {
            return $this->emptyAnalyticsView(
                $academicYears, $exams, $standards, $divisions, $request
            );
        }

        $allowedSubjectIds = $this->allowedSubjectIds($examId ? (int) $examId : null, (int) $standardId);

        /* ---------- Base query (excludes optional) ---------- */
        $buildBase = function () use ($yearId, $examId, $standardId, $divisionId, $allowedSubjectIds) {
            $q = StudentMark::query()
                ->where('student_marks.is_optional', 0);

            if ($yearId)     $q->where('student_marks.academic_year_id', $yearId);
            if ($examId)     $q->where('student_marks.exam_master_id',  $examId);
            if ($standardId) $q->where('student_marks.standard_id',     $standardId);
            if ($divisionId) $q->where('student_marks.division_id',     $divisionId);

            if (!empty($allowedSubjectIds)) {
                $q->whereIn(
                    DB::raw('(' . $this->resolvedSubjectIdSql() . ')'),
                    $allowedSubjectIds
                );
            }

            return $q;
        };

        /* ---------- KPI ---------- */
        $totalStudents = $buildBase()->distinct('student_marks.student_id')->count('student_marks.student_id');

        $averageMarks = $buildBase()->selectRaw("
            AVG(COALESCE(student_marks.theory_obtained_marks,0)
              + COALESCE(student_marks.oral_obtained_marks,0)
              + COALESCE(student_marks.practical_obtained_marks,0)) as avg_marks
        ")->value('avg_marks') ?? 0;

        $highestMarks = $buildBase()->selectRaw("
            MAX(COALESCE(student_marks.theory_obtained_marks,0)
              + COALESCE(student_marks.oral_obtained_marks,0)
              + COALESCE(student_marks.practical_obtained_marks,0)) as max_marks
        ")->value('max_marks') ?? 0;

        $lowestMarks = $buildBase()->selectRaw("
            MIN(COALESCE(student_marks.theory_obtained_marks,0)
              + COALESCE(student_marks.oral_obtained_marks,0)
              + COALESCE(student_marks.practical_obtained_marks,0)) as min_marks
        ")->value('min_marks') ?? 0;

        /* ---------- Subject analysis (chart) ---------- */
        $subjectAnalysis = $buildBase()
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->leftJoin('subject_types', 'subject_types.id', '=', 'subjects.subject_type_id')
            ->select(
                'subjects.id as subject_id',
                DB::raw("CASE WHEN subject_types.type_name = 'SKILL'
                              THEN 'SKILL SUBJECT'
                              ELSE subjects.subject_name END as display_subject"),
                DB::raw("AVG(COALESCE(student_marks.theory_obtained_marks,0)
                              + COALESCE(student_marks.oral_obtained_marks,0)
                              + COALESCE(student_marks.practical_obtained_marks,0)) as avg_marks")
            )
            ->groupBy('subjects.id', 'display_subject')
            ->orderBy('display_subject')
            ->get();

        /* ---------- Subject-wise pass % ---------- */
        $subjectStats = $buildBase()
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->select(
                'subjects.id as subject_id',
                'subjects.subject_name',
                DB::raw("COUNT(DISTINCT student_marks.student_id) as total_count"),
                DB::raw("COUNT(DISTINCT CASE WHEN
                        (
                          (COALESCE(student_marks.theory_obtained_marks,0)
                         + COALESCE(student_marks.oral_obtained_marks,0)
                         + COALESCE(student_marks.practical_obtained_marks,0))
                          /
                          NULLIF(COALESCE(student_marks.theory_max_marks,0)
                               + COALESCE(student_marks.oral_max_marks,0)
                               + COALESCE(student_marks.practical_max_marks,0),0)
                        ) * 100 >= 35
                        THEN student_marks.student_id END) as pass_count")
            )
            ->groupBy('subjects.id', 'subjects.subject_name')
            ->orderBy('subjects.subject_name')
            ->get()
            ->map(function ($row) {
                $row->pass_percentage = $row->total_count > 0
                    ? round(($row->pass_count / $row->total_count) * 100, 1)
                    : 0;
                return $row;
            });

        /* ---------- Standard-wise comparison ---------- */
        $standardComparison = $buildBase()
            ->join('standards', 'standards.id', '=', 'student_marks.standard_id')
            ->select(
                'standards.standard_name',
                DB::raw("AVG(
                    COALESCE(student_marks.theory_obtained_marks,0)
                  + COALESCE(student_marks.oral_obtained_marks,0)
                  + COALESCE(student_marks.practical_obtained_marks,0)) as avg_marks"),
                DB::raw("COUNT(DISTINCT student_marks.student_id) as student_count")
            )
            ->groupBy('standards.standard_name')
            ->orderBy('standards.standard_name')
            ->get();

        /* ---------- Pass / Fail ---------- */
        $passStudents = $buildBase()
            ->select('student_marks.student_id')
            ->groupBy('student_marks.student_id')
            ->havingRaw("
                (
                    SUM(COALESCE(student_marks.theory_obtained_marks,0)
                      + COALESCE(student_marks.oral_obtained_marks,0)
                      + COALESCE(student_marks.practical_obtained_marks,0))
                    /
                    NULLIF(SUM(COALESCE(student_marks.theory_max_marks,0)
                             + COALESCE(student_marks.oral_max_marks,0)
                             + COALESCE(student_marks.practical_max_marks,0)),0)
                ) * 100 >= 35
            ")
            ->get()->count();

        $passPercentage = $totalStudents > 0 ? ($passStudents / $totalStudents) * 100 : 0;
        $failPercentage = 100 - $passPercentage;

        /* ---------- Top 10 Toppers ---------- */
        $topStudents = $buildBase()
            ->select(
                'student_marks.student_id',
                DB::raw("SUM(COALESCE(student_marks.theory_obtained_marks,0)
                            + COALESCE(student_marks.oral_obtained_marks,0)
                            + COALESCE(student_marks.practical_obtained_marks,0)) as total_marks")
            )
            ->groupBy('student_marks.student_id')
            ->orderByDesc('total_marks')
            ->limit(10)
            ->get();

        /* ---------- Top 10 Weak Students ---------- */
        $riskStudents = $buildBase()
            ->select(
                'student_marks.student_id',
                DB::raw("AVG(COALESCE(student_marks.theory_obtained_marks,0)
                            + COALESCE(student_marks.oral_obtained_marks,0)
                            + COALESCE(student_marks.practical_obtained_marks,0)) as avg_marks")
            )
            ->groupBy('student_marks.student_id')
            ->havingRaw('avg_marks < 40')
            ->orderBy('avg_marks')
            ->limit(10)
            ->get();

        /* ---------- ERP names ---------- */
        $studentMap = StudentHelper::getStudentsDirectERP(
            $request->academic_year_id,
            $request->standard_id,
            $request->division_id
        )->keyBy('Studentid');

        $attachName = function ($row) use ($studentMap) {
            $s = $studentMap->get($row->student_id);
            $row->student_name = $s ? $s->full_name : 'Unknown Student';
            return $row;
        };

        $topStudents  = $topStudents->map($attachName);
        $riskStudents = $riskStudents->map($attachName);

        /* ---------- Grade distribution ---------- */
        $gradeCounts = ['A1'=>0,'A2'=>0,'B1'=>0,'B2'=>0,'C1'=>0,'C2'=>0,'D'=>0,'E'=>0,'Absent'=>0,'Left'=>0];

        $studentGrades = $buildBase()
            ->select(
                'student_marks.student_id',
                DB::raw("MAX(COALESCE(student_marks.is_absent,0)) as is_absent"),
                DB::raw("(
                    SUM(COALESCE(student_marks.theory_obtained_marks,0)
                      + COALESCE(student_marks.oral_obtained_marks,0)
                      + COALESCE(student_marks.practical_obtained_marks,0))
                    /
                    NULLIF(SUM(COALESCE(student_marks.theory_max_marks,0)
                             + COALESCE(student_marks.oral_max_marks,0)
                             + COALESCE(student_marks.practical_max_marks,0)),0)
                ) * 100 as percentage")
            )
            ->groupBy('student_marks.student_id')
            ->get();

        foreach ($studentGrades as $s) {
            if (($s->is_absent ?? 0) == 1) { $gradeCounts['Absent']++; continue; }
            $p = $s->percentage;
            if     ($p >= 91) $gradeCounts['A1']++;
            elseif ($p >= 81) $gradeCounts['A2']++;
            elseif ($p >= 71) $gradeCounts['B1']++;
            elseif ($p >= 61) $gradeCounts['B2']++;
            elseif ($p >= 51) $gradeCounts['C1']++;
            elseif ($p >= 41) $gradeCounts['C2']++;
            elseif ($p >= 35) $gradeCounts['D']++;
            else              $gradeCounts['E']++;
        }

        /* ---------- Chart data ---------- */
        $subjectLabels = $subjectAnalysis->pluck('display_subject')->toArray();
        $subjectMarks  = $subjectAnalysis->pluck('avg_marks')->map(fn($m) => round($m, 2))->toArray();

        $topStudentNames = $topStudents->pluck('student_name')->toArray();
        $topStudentMarks = $topStudents->pluck('total_marks')->map(fn($m) => round($m, 2))->toArray();

        return view('analytics.index', [
            'hasFilters'      => true,
            'gradeCounts'     => $gradeCounts,
            'academicYears'   => $academicYears,
            'exams'           => $exams,
            'examId'          => $examId,
            'standards'       => $standards,
            'divisions'       => $divisions,
            'standardId'      => $standardId,
            'divisionId'      => $divisionId,
            'yearId'          => $yearId,
            'totalStudents'   => $totalStudents,
            'averageMarks'    => $averageMarks,
            'highestMarks'    => $highestMarks,
            'lowestMarks'     => $lowestMarks,
            'passPercentage'  => $passPercentage,
            'failPercentage'  => $failPercentage,
            'subjectAnalysis' => $subjectAnalysis,
            'topStudents'     => $topStudents,
            'riskStudents'    => $riskStudents,
            'subjectLabels'   => $subjectLabels,
            'subjectMarks'    => $subjectMarks,
            'topStudentNames' => $topStudentNames,
            'topStudentMarks' => $topStudentMarks,
            'subjectStats'    => $subjectStats,
            'standardComparison' => $standardComparison,
        ]);
    }


    /* =====================================================================
     |  STUDENT DETAIL
     | ===================================================================== */
    public function studentDetail(Request $request, $studentId)
    {
        $yearId = $request->academic_year_id;
        $examId = $request->exam_master_id;

        $anchor = StudentMark::where('student_marks.student_id', $studentId)
            ->when($yearId, fn($q) => $q->where('student_marks.academic_year_id', $yearId))
            ->first();

        if (!$anchor) {
            return redirect()->route('analytics.index')
                ->with('error', 'No marks found for this student.');
        }

        $standardId = $anchor->standard_id;
        $divisionId = $anchor->division_id;
        $yearId     = $yearId ?: $anchor->academic_year_id;

        /* ---------- Names ---------- */
        $yearName     = optional(AcademicYear::find($yearId))->year_name     ?? $yearId;
        $standardName = optional(Standard::find($standardId))->standard_name ?? $standardId;
        $divisionName = optional(Division::find($divisionId))->division_name ?? $divisionId;
        $examName     = $examId
            ? (optional(ExamMaster::find($examId))->exam_name ?? null)
            : 'All Exams';

        /* ---------- Allowed subjects for this exam ---------- */
        $allowedSubjectIds = $this->allowedSubjectIds($examId ? (int) $examId : null, (int) $standardId);

        /* ---------- Student name ---------- */
        $studentName = 'Unknown Student';
        $erpStudents = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId);
        $match = collect($erpStudents)->firstWhere('Studentid', $studentId)
              ?? collect($erpStudents)->firstWhere('Studentid', (string) $studentId);
        if ($match) $studentName = $match->full_name ?? $match->studname ?? $studentName;

        /* ---------- Subject-wise marks for THIS student ---------- */
        $subjectMarksQuery = StudentMark::where('student_marks.student_id', $studentId)
            ->where('student_marks.is_optional', 0)
            ->when($yearId, fn($q) => $q->where('student_marks.academic_year_id', $yearId))
            ->when($examId, fn($q) => $q->where('student_marks.exam_master_id', $examId))
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->select(
                'subjects.id as subject_id',
                'subjects.subject_name',
                'student_marks.theory_obtained_marks',
                'student_marks.oral_obtained_marks',
                'student_marks.practical_obtained_marks',
                'student_marks.theory_max_marks',
                'student_marks.oral_max_marks',
                'student_marks.practical_max_marks',
                'student_marks.is_absent',
                'student_marks.is_optional'
            );

        if (!empty($allowedSubjectIds)) {
            $subjectMarksQuery->whereIn('subjects.id', $allowedSubjectIds);
        }

        $subjectMarks = $subjectMarksQuery
            ->orderBy('subjects.subject_name')
            ->get();

        $subjectMarks = $this->attachStructureMaxes($subjectMarks, $standardId, $examName ?? '');

        $subjectMarks = $subjectMarks
            ->unique('subject_id')
            ->values();

        $totalObtained = $subjectMarks->sum('obtained');
        $totalMax      = $subjectMarks->sum('max');
        $percentage    = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

        $grade = 'E';
        if     ($percentage >= 91) $grade = 'A1';
        elseif ($percentage >= 81) $grade = 'A2';
        elseif ($percentage >= 71) $grade = 'B1';
        elseif ($percentage >= 61) $grade = 'B2';
        elseif ($percentage >= 51) $grade = 'C1';
        elseif ($percentage >= 41) $grade = 'C2';
        elseif ($percentage >= 35) $grade = 'D';

        /* ---------- Class totals ---------- */
        $classDataQuery = StudentMark::where('student_marks.is_optional', 0)
            ->when($yearId, fn($q) => $q->where('student_marks.academic_year_id', $yearId))
            ->when($examId, fn($q) => $q->where('student_marks.exam_master_id', $examId))
            ->where('student_marks.standard_id', $standardId)
            ->where('student_marks.division_id', $divisionId)
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->select(
                'student_marks.student_id',
                'subjects.id as subject_id',
                'subjects.subject_name',
                'student_marks.theory_obtained_marks',
                'student_marks.oral_obtained_marks',
                'student_marks.practical_obtained_marks',
                'student_marks.theory_max_marks',
                'student_marks.oral_max_marks',
                'student_marks.practical_max_marks'
            );

        if (!empty($allowedSubjectIds)) {
            $classDataQuery->whereIn('subjects.id', $allowedSubjectIds);
        }

        $classDataRaw = $classDataQuery->get();
        $classDataRaw = $this->attachStructureMaxes($classDataRaw, $standardId, $examName ?? '');

        $classDataRaw = $classDataRaw->unique(function ($r) {
            return $r->student_id . '|' . $r->subject_id;
        })->values();

        $classData = $classDataRaw
            ->groupBy('student_id')
            ->map(function ($rows) {
                return (object) [
                    'student_id'     => $rows->first()->student_id,
                    'total_obtained' => $rows->sum('obtained'),
                    'total_max'      => $rows->sum('max'),
                ];
            })
            ->values();

        $classAverage = $classData->avg('total_obtained') ?? 0;
        $classAverage = round($classAverage, 2);

        $classPercentAvg = $classData->avg(function ($r) {
            return $r->total_max > 0 ? ($r->total_obtained / $r->total_max) * 100 : 0;
        }) ?? 0;
        $classPercentAvg = round($classPercentAvg, 2);

        $rank = 1;
        foreach ($classData->sortByDesc('total_obtained')->values() as $i => $row) {
            if ($row->student_id == $studentId) { $rank = $i + 1; break; }
        }

        /* ---------- Exam-wise trend ---------- */
        $examTrendRaw = StudentMark::where('student_marks.student_id', $studentId)
            ->where('student_marks.is_optional', 0)
            ->when($yearId, fn($q) => $q->where('student_marks.academic_year_id', $yearId))
            ->join('exam_masters', 'exam_masters.id', '=', 'student_marks.exam_master_id')
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->select(
                'exam_masters.id as exam_id',
                'exam_masters.exam_name',
                'exam_masters.display_order',
                'subjects.id as subject_id',
                'subjects.subject_name',
                'student_marks.theory_obtained_marks',
                'student_marks.oral_obtained_marks',
                'student_marks.practical_obtained_marks',
                'student_marks.theory_max_marks',
                'student_marks.oral_max_marks',
                'student_marks.practical_max_marks'
            )
            ->get();

        $examTrendRaw = $examTrendRaw->filter(function ($r) {
            if (!$r->exam_id) return true;
            return DB::table('exam_master_subjects')
                ->where('exam_master_id', $r->exam_id)
                ->where('subject_id', $r->subject_id)
                ->exists();
        })->values();

        $examTrend = $examTrendRaw
            ->groupBy('exam_id')
            ->map(function ($rows) use ($standardId) {
                $examName = $rows->first()->exam_name;
                $corrected = $this->attachStructureMaxes($rows, $standardId, $examName);

                $corrected = $corrected->unique(function ($r) {
                    return $r->exam_id . '|' . $r->subject_id;
                })->values();

                $obtained = $corrected->sum('obtained');
                $max      = $corrected->sum('max');

                return (object) [
                    'exam_name'    => $examName,
                    'display_order'=> $rows->first()->display_order,
                    'obtained'     => $obtained,
                    'max_marks'    => $max,
                    'percent'      => $max > 0 ? round(($obtained / $max) * 100, 2) : 0,
                ];
            })
            ->sortBy('display_order')
            ->values();

        /* ---------- Subject-wise class average ---------- */
        $classSubjectAvgQuery = StudentMark::where('student_marks.is_optional', 0)
            ->when($yearId, fn($q) => $q->where('student_marks.academic_year_id', $yearId))
            ->when($examId, fn($q) => $q->where('student_marks.exam_master_id', $examId))
            ->where('student_marks.standard_id', $standardId)
            ->where('student_marks.division_id', $divisionId)
            ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
            ->select(
                'subjects.id as subject_id',
                'subjects.subject_name',
                'student_marks.theory_obtained_marks',
                'student_marks.oral_obtained_marks',
                'student_marks.practical_obtained_marks'
            );

        if (!empty($allowedSubjectIds)) {
            $classSubjectAvgQuery->whereIn('subjects.id', $allowedSubjectIds);
        }

        $classSubjectAvgRaw = $classSubjectAvgQuery->get();

        $classSubjectAvg = $classSubjectAvgRaw
            ->groupBy('subject_name')
            ->map(function ($rows) {
                $total = $rows->sum(function ($r) {
                    return (float) ($r->theory_obtained_marks ?? 0)
                         + (float) ($r->oral_obtained_marks ?? 0)
                         + (float) ($r->practical_obtained_marks ?? 0);
                });
                return round($total / max($rows->count(), 1), 2);
            });

        /* ---------- Comparison data ---------- */
        $seen = [];
        $subjectComparison = $subjectMarks->map(function ($r) use ($classSubjectAvg, &$seen) {
            $label = $r->subject_name;
            if (isset($seen[$label])) {
                $seen[$label]++;
                $label .= ' (' . $seen[$label] . ')';
            } else {
                $seen[$label] = 1;
            }
            return [
                'subject'   => $label,
                'student'   => round($r->obtained, 2),
                'class_avg' => round($classSubjectAvg[$r->subject_name] ?? 0, 2),
            ];
        });

        return view('analytics.student', compact(
            'studentId','studentName','yearId','yearName',
            'standardId','standardName','divisionId','divisionName',
            'examId','examName',
            'subjectMarks','totalObtained','totalMax','percentage','grade',
            'rank','classAverage','classPercentAvg',
            'examTrend','subjectComparison'
        ));
    }


    /* =====================================================================
     |  SUBJECT DETAIL
     | ===================================================================== */
    public function subjectDetail(Request $request, $subjectId)
    {
        $yearId     = $request->academic_year_id;
        $standardId = $request->standard_id;
        $divisionId = $request->division_id;
        $examId     = $request->exam_master_id;

        if (!$yearId || !$standardId || !$divisionId) {
            return redirect()->route('analytics.index')
                ->with('error', 'Please apply filters first.');
        }

        $subjectName  = optional(\App\Models\Subject::find($subjectId))->subject_name ?? 'Subject';
        $yearName     = optional(AcademicYear::find($yearId))->year_name     ?? $yearId;
        $standardName = optional(Standard::find($standardId))->standard_name ?? $standardId;
        $divisionName = optional(Division::find($divisionId))->division_name ?? $divisionId;
        $examName     = $examId
            ? (optional(ExamMaster::find($examId))->exam_name ?? null)
            : 'All Exams';

        $allowedSubjectIds = $this->allowedSubjectIds(
            $examId ? (int) $examId : null,
            (int) $standardId
        );

        $structure = ExamStructureHelper::getComponentValues(
            (int) $standardId,
            $examName ?? '',
            $subjectName
        );

        $subjectMaxTotal     = (float) ($structure['total_max_marks']     ?? 0);
        $subjectPassingTotal = (float) ($structure['total_passing_marks'] ?? 0);

        if ($subjectMaxTotal <= 0 && $examId) {
            $cfg = DB::table('exam_master_subjects')
                ->where('exam_master_id', $examId)
                ->where('standard_id',    $standardId)
                ->where('subject_id',     $subjectId)
                ->first();

            if ($cfg) {
                $subjectMaxTotal     = (float) ($cfg->max_marks     ?? 0);
                $subjectPassingTotal = (float) ($cfg->passing_marks ?? 0);
            }
        }

        if ($subjectMaxTotal <= 0) {
            $subjectMaxTotal = 40.0;
        }

        if ($subjectPassingTotal <= 0) {
            $subjectPassingTotal = (int) ceil($subjectMaxTotal * 0.35);
        }

        $swsIds = DB::table('standard_wise_subjects')
            ->where('subject_id', $subjectId)
            ->where('standard_id', $standardId)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $rowsQuery = StudentMark::where('student_marks.academic_year_id', $yearId)
            ->where('student_marks.standard_id', $standardId)
            ->where('student_marks.division_id', $divisionId)
            ->where('student_marks.is_optional', 0)
            ->when($examId, fn($q) => $q->where('student_marks.exam_master_id', $examId))
            ->where(function ($q) use ($subjectId, $swsIds) {
                $q->where('student_marks.subject_id', $subjectId);
                if (!empty($swsIds)) {
                    $q->orWhereIn('student_marks.subject_id', $swsIds);
                }
            });

        if (!empty($allowedSubjectIds)) {
            $rowsQuery->whereIn(
                DB::raw('(' . $this->resolvedSubjectIdSql() . ')'),
                $allowedSubjectIds
            );
        }

        $rows = $rowsQuery
            ->select(
                'student_marks.student_id',
                DB::raw("SUM(COALESCE(student_marks.theory_obtained_marks,0)
                            + COALESCE(student_marks.oral_obtained_marks,0)
                            + COALESCE(student_marks.practical_obtained_marks,0)) as obtained")
            )
            ->groupBy('student_marks.student_id')
            ->get()
            ->map(function ($r) use ($subjectMaxTotal, $subjectPassingTotal) {
                $r->max_marks = $subjectMaxTotal;
                $r->passing   = $subjectPassingTotal;
                $r->percent   = $subjectMaxTotal > 0
                    ? round(($r->obtained / $subjectMaxTotal) * 100, 2)
                    : 0;
                return $r;
            });

        $studentMap = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId)
            ->keyBy('Studentid');

        $rows = $rows->map(function ($r) use ($studentMap) {
            $s = $studentMap->get($r->student_id);
            $r->student_name = $s ? $s->full_name : 'Unknown Student';
            return $r;
        });

        $totalCount = $rows->count();
        $passed     = $rows->filter(fn($r) => $r->obtained >= $subjectPassingTotal)->count();
        $failed     = $totalCount - $passed;
        $passPct    = $totalCount > 0 ? round(($passed / $totalCount) * 100, 1) : 0;
        $classAvg   = $totalCount > 0 ? round($rows->avg('obtained'), 2) : 0;

        $toppers = $rows->sortByDesc('obtained')->take(10)->values();
        $weak    = $rows->where('percent', '<', 40)->sortBy('obtained')->values();

        return view('analytics.subject', compact(
            'subjectId','subjectName',
            'yearId','yearName','standardId','standardName',
            'divisionId','divisionName','examId','examName',
            'rows','toppers','weak',
            'totalCount','passed','failed','passPct','classAvg'
        ));
    }


    /* =====================================================================
     |  Placeholder view when no filters are applied
     | ===================================================================== */
    private function emptyAnalyticsView($academicYears, $exams, $standards, $divisions, Request $request)
    {
        return view('analytics.index', [
            'hasFilters'      => false,
            'academicYears'   => $academicYears,
            'exams'           => $exams,
            'standards'       => $standards,
            'divisions'       => $divisions,
            'yearId'          => $request->academic_year_id,
            'standardId'      => $request->standard_id,
            'divisionId'      => $request->division_id,
            'examId'          => $request->exam_master_id,
            'gradeCounts'     => [
                'A1'=>0,'A2'=>0,'B1'=>0,'B2'=>0,'C1'=>0,'C2'=>0,
                'D'=>0,'E'=>0,'Absent'=>0,'Left'=>0,
            ],
            'totalStudents'   => 0,
            'averageMarks'    => 0,
            'highestMarks'    => 0,
            'lowestMarks'     => 0,
            'passPercentage'  => 0,
            'failPercentage'  => 0,
            'subjectAnalysis' => collect(),
            'topStudents'     => collect(),
            'riskStudents'    => collect(),
            'subjectLabels'   => [],
            'subjectMarks'    => [],
            'topStudentNames' => [],
            'topStudentMarks' => [],
            'subjectStats'    => collect(),
            'standardComparison' => collect(),
        ]);
    }


    /* =====================================================================
     |  COMPARISON — Compare two exams (percentage based)
     | ===================================================================== */
    public function comparison(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $standards     = Standard::orderBy('display_order')->get();
        $divisions     = Division::orderBy('display_order')->get();
        $exams         = ExamMaster::orderByDesc('id')->get();

        $hasFilters = $request->filled([
            'standard_id', 'division_id',
            'year1_id', 'exam1_id',
            'year2_id', 'exam2_id',
        ]);

        if (!$hasFilters) {
            return view('analytics.comparison', compact(
                'academicYears', 'standards', 'divisions', 'exams'
            ) + ['hasFilters' => false]);
        }

        $standardId = (int) $request->standard_id;
        $divisionId = (int) $request->division_id;
        $year1Id    = (int) $request->year1_id;
        $exam1Id    = (int) $request->exam1_id;
        $year2Id    = (int) $request->year2_id;
        $exam2Id    = (int) $request->exam2_id;

        $year1Model = AcademicYear::find($year1Id);
        $year2Model = AcademicYear::find($year2Id);
        $exam1Model = ExamMaster::find($exam1Id);
        $exam2Model = ExamMaster::find($exam2Id);

        $exam1Name = $exam1Model->exam_name ?? 'Exam 1';
        $exam2Name = $exam2Model->exam_name ?? 'Exam 2';

        /* ---- Students of the class (from year 1 as anchor) ---- */
        $studentMap = collect();
        try {
            $erpStudents = StudentHelper::getStudentsDirectERP($year1Id, $standardId, $divisionId);
            foreach ($erpStudents as $s) {
                $studentMap->put((string) $s->Studentid, [
                    'id'   => (string) $s->Studentid,
                    'name' => $s->full_name ?? trim(($s->studname ?? '') . ' ' . ($s->fathername ?? '')),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        /* Also include any student that has marks in either exam */
        $unionStudentIds = StudentMark::where(function ($q) use ($exam1Id, $exam2Id) {
                $q->where('exam_master_id', $exam1Id)
                  ->orWhere('exam_master_id', $exam2Id);
            })
            ->where('standard_id', $standardId)
            ->where('division_id', $divisionId)
            ->distinct('student_id')
            ->pluck('student_id')
            ->map(fn($v) => (string) $v)
            ->all();

        foreach ($unionStudentIds as $sid) {
            if (!$studentMap->has($sid)) {
                $studentMap->put($sid, ['id' => $sid, 'name' => 'Student ' . $sid]);
            }
        }

        $studentIds = $studentMap->keys()->all();

        if (empty($studentIds)) {
            return view('analytics.comparison', compact(
                'academicYears', 'standards', 'divisions', 'exams'
            ) + [
                'hasFilters' => true,
                'emptyMsg'   => 'No students found in this class.',
            ]);
        }

        /* ---- Fetch marks for both exams ---- */
        $allowed1 = $this->allowedSubjectIds($exam1Id, $standardId);
        $allowed2 = $this->allowedSubjectIds($exam2Id, $standardId);

        $fetchMarks = function ($examId, $allowed) use ($studentIds, $standardId) {
            $q = StudentMark::where('student_marks.exam_master_id', $examId)
                ->where('student_marks.standard_id', $standardId)
                ->whereIn('student_marks.student_id', $studentIds)
                ->where('student_marks.is_optional', 0)
                ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
                ->select(
                    'student_marks.student_id',
                    'subjects.id as subject_id',
                    'subjects.subject_name',
                    'student_marks.theory_obtained_marks',
                    'student_marks.oral_obtained_marks',
                    'student_marks.practical_obtained_marks',
                    'student_marks.theory_max_marks',
                    'student_marks.oral_max_marks',
                    'student_marks.practical_max_marks'
                );

            if (!empty($allowed)) {
                $q->whereIn('subjects.id', $allowed);
            }

            return $q->get();
        };

        $marks1Raw = $fetchMarks($exam1Id, $allowed1);
        $marks2Raw = $fetchMarks($exam2Id, $allowed2);

        $marks1 = $this->attachStructureMaxes($marks1Raw, $standardId, $exam1Name)
            ->unique(fn($r) => $r->student_id . '|' . $r->subject_id)
            ->groupBy('student_id');

        $marks2 = $this->attachStructureMaxes($marks2Raw, $standardId, $exam2Name)
            ->unique(fn($r) => $r->student_id . '|' . $r->subject_id)
            ->groupBy('student_id');

        /* ---- Per-student comparison ---- */
        $studentComparison = collect();

        foreach ($studentMap as $sid => $info) {
            $rows1 = $marks1[$sid] ?? collect();
            $rows2 = $marks2[$sid] ?? collect();

            $t1Obtained = $rows1->sum('obtained');
            $t1Max      = $rows1->sum('max');
            $t2Obtained = $rows2->sum('obtained');
            $t2Max      = $rows2->sum('max');

            $e1Pct = $t1Max > 0 ? round(($t1Obtained / $t1Max) * 100, 2) : null;
            $e2Pct = $t2Max > 0 ? round(($t2Obtained / $t2Max) * 100, 2) : null;

            $delta = ($e1Pct !== null && $e2Pct !== null) ? round($e2Pct - $e1Pct, 2) : null;

            $status = 'same';
            if ($delta !== null) {
                if ($delta > 0.5)      $status = 'improved';
                elseif ($delta < -0.5) $status = 'declined';
            }

            $studentComparison->push((object) [
                'id'         => $sid,
                'name'       => $info['name'],
                'e1_total'   => round($t1Obtained, 2),
                'e1_max'     => round($t1Max, 2),
                'e1_pct'     => $e1Pct,
                'e2_total'   => round($t2Obtained, 2),
                'e2_max'     => round($t2Max, 2),
                'e2_pct'     => $e2Pct,
                'delta'      => $delta,
                'status'     => $status,
                'both_exams' => ($rows1->isNotEmpty() && $rows2->isNotEmpty()),
            ]);
        }

        $studentComparison = $studentComparison
            ->sortByDesc(fn($s) => $s->e2_pct ?? -1)
            ->values();

        /* ---- Subject-wise comparison ---- */
        $allSubjectIds = collect($allowed1)->merge($allowed2)->unique()->values();
        $allSubjects   = \App\Models\Subject::whereIn('id', $allSubjectIds)
            ->orderBy('subject_name')->get();

        $subjectComparison = collect();

        foreach ($allSubjects as $sub) {
            $sid = $sub->id;

            $r1 = $marks1Raw->where('subject_id', $sid);
            $r2 = $marks2Raw->where('subject_id', $sid);

            $sum1 = $r1->sum(fn($r) =>
                (float) ($r->theory_obtained_marks ?? 0)
              + (float) ($r->oral_obtained_marks ?? 0)
              + (float) ($r->practical_obtained_marks ?? 0));

            $sum2 = $r2->sum(fn($r) =>
                (float) ($r->theory_obtained_marks ?? 0)
              + (float) ($r->oral_obtained_marks ?? 0)
              + (float) ($r->practical_obtained_marks ?? 0));

            $corrected1 = $this->attachStructureMaxes($r1, $standardId, $exam1Name)
                ->unique(fn($r) => $r->student_id . '|' . $r->subject_id);
            $corrected2 = $this->attachStructureMaxes($r2, $standardId, $exam2Name)
                ->unique(fn($r) => $r->student_id . '|' . $r->subject_id);

            $max1  = $corrected1->sum('max');
            $max2  = $corrected2->sum('max');
            $pass1 = $corrected1->filter(fn($r) => $r->obtained >= $r->passing)->count();
            $pass2 = $corrected2->filter(fn($r) => $r->obtained >= $r->passing)->count();

            $avg1Pct = $max1 > 0 ? round(($sum1 / $max1) * 100, 2) : null;
            $avg2Pct = $max2 > 0 ? round(($sum2 / $max2) * 100, 2) : null;
            $delta   = ($avg1Pct !== null && $avg2Pct !== null) ? round($avg2Pct - $avg1Pct, 2) : null;

            $subjectComparison->push((object) [
                'subject_id'   => $sid,
                'subject_name' => $sub->subject_name,
                'max1'  => $max1,
                'max2'  => $max2,
                'pass1' => $corrected1->first()->passing ?? null,
                'pass2' => $corrected2->first()->passing ?? null,
                'avg1_pct'  => $avg1Pct,
                'avg2_pct'  => $avg2Pct,
                'pass1_pct' => $corrected1->count() > 0 ? round(($pass1 / $corrected1->count()) * 100, 1) : null,
                'pass2_pct' => $corrected2->count() > 0 ? round(($pass2 / $corrected2->count()) * 100, 1) : null,
                'delta'     => $delta,
                'students1' => $corrected1->count(),
                'students2' => $corrected2->count(),
            ]);
        }

        /* ---- KPI ---- */
        $exam1Pcts = $studentComparison->pluck('e1_pct')->filter(fn($v) => $v !== null);
        $exam2Pcts = $studentComparison->pluck('e2_pct')->filter(fn($v) => $v !== null);
        $passThreshold = 35;

        $kpi = [
            'exam1' => [
                'label'      => $exam1Name,
                'year_label' => $year1Model->year_name ?? '',
                'total'      => $exam1Pcts->count(),
                'avg_pct'    => $exam1Pcts->avg() ?? 0,
                'highest'    => $exam1Pcts->max() ?? 0,
                'lowest'     => $exam1Pcts->min() ?? 0,
                'pass_count' => $exam1Pcts->filter(fn($p) => $p >= $passThreshold)->count(),
            ],
            'exam2' => [
                'label'      => $exam2Name,
                'year_label' => $year2Model->year_name ?? '',
                'total'      => $exam2Pcts->count(),
                'avg_pct'    => $exam2Pcts->avg() ?? 0,
                'highest'    => $exam2Pcts->max() ?? 0,
                'lowest'     => $exam2Pcts->min() ?? 0,
                'pass_count' => $exam2Pcts->filter(fn($p) => $p >= $passThreshold)->count(),
            ],
        ];

        $kpi['exam1']['pass_pct'] = $kpi['exam1']['total'] > 0
            ? round(($kpi['exam1']['pass_count'] / $kpi['exam1']['total']) * 100, 1) : 0;
        $kpi['exam2']['pass_pct'] = $kpi['exam2']['total'] > 0
            ? round(($kpi['exam2']['pass_count'] / $kpi['exam2']['total']) * 100, 1) : 0;

        $kpi['delta'] = [
            'avg_pct'  => round($kpi['exam2']['avg_pct']  - $kpi['exam1']['avg_pct'], 2),
            'highest'  => round($kpi['exam2']['highest']  - $kpi['exam1']['highest'], 2),
            'lowest'   => round($kpi['exam2']['lowest']   - $kpi['exam1']['lowest'],  2),
            'pass_pct' => round($kpi['exam2']['pass_pct'] - $kpi['exam1']['pass_pct'], 2),
        ];

        /* ---- Improvers / Decliners ---- */
        $improvers = $studentComparison->where('both_exams', true)
            ->whereNotNull('delta')->sortByDesc('delta')->take(10)->values();
        $decliners = $studentComparison->where('both_exams', true)
            ->whereNotNull('delta')->sortBy('delta')->take(10)->values();

        /* ---- Chart data ---- */
        $chartSubjectLabels = $subjectComparison->pluck('subject_name')->all();
        $chartSubjectPct1   = $subjectComparison->map(fn($s) => $s->avg1_pct ?? 0)->all();
        $chartSubjectPct2   = $subjectComparison->map(fn($s) => $s->avg2_pct ?? 0)->all();

        $improvedCount = $studentComparison->where('status', 'improved')->count();
        $declinedCount = $studentComparison->where('status', 'declined')->count();
        $sameCount     = $studentComparison->where('status', 'same')->count();

        return view('analytics.comparison', compact(
            'academicYears', 'standards', 'divisions', 'exams',
            'year1Id', 'year2Id', 'exam1Id', 'exam2Id',
            'standardId', 'divisionId',
            'studentComparison', 'subjectComparison', 'kpi',
            'improvers', 'decliners',
            'chartSubjectLabels', 'chartSubjectPct1', 'chartSubjectPct2',
            'improvedCount', 'declinedCount', 'sameCount'
        ) + ['hasFilters' => true]);
    }


    /* =====================================================================
     |  COMPARISON STUDENT DETAIL — modal JSON
     | ===================================================================== */
    public function comparisonStudentDetail(Request $request, $studentId)
    {
        $exam1Id    = (int) $request->input('exam1_id');
        $exam2Id    = (int) $request->input('exam2_id');
        $standardId = (int) $request->input('standard_id');

        $exam1Model = ExamMaster::find($exam1Id);
        $exam2Model = ExamMaster::find($exam2Id);
        $exam1Name  = $exam1Model->exam_name ?? '';
        $exam2Name  = $exam2Model->exam_name ?? '';

        $allowed1 = $this->allowedSubjectIds($exam1Id, $standardId);
        $allowed2 = $this->allowedSubjectIds($exam2Id, $standardId);

        $fetch = function ($examId, $allowed) use ($studentId, $standardId) {
            $q = StudentMark::where('student_marks.exam_master_id', $examId)
                ->where('student_marks.standard_id', $standardId)
                ->where('student_marks.student_id', $studentId)
                ->where('student_marks.is_optional', 0)
                ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
                ->select(
                    'subjects.id as subject_id',
                    'subjects.subject_name',
                    'student_marks.theory_obtained_marks',
                    'student_marks.oral_obtained_marks',
                    'student_marks.practical_obtained_marks',
                    'student_marks.theory_max_marks',
                    'student_marks.oral_max_marks',
                    'student_marks.practical_max_marks'
                );

            if (!empty($allowed)) {
                $q->whereIn('subjects.id', $allowed);
            }

            return $q->get();
        };

        $raw1 = $this->attachStructureMaxes($fetch($exam1Id, $allowed1), $standardId, $exam1Name)
            ->unique('subject_id')->keyBy('subject_id');

        $raw2 = $this->attachStructureMaxes($fetch($exam2Id, $allowed2), $standardId, $exam2Name)
            ->unique('subject_id')->keyBy('subject_id');

        $allSubjectIds = $raw1->keys()->merge($raw2->keys())->unique()->values();
        $allSubjects   = \App\Models\Subject::whereIn('id', $allSubjectIds)
            ->orderBy('subject_name')->get();

        $rows = [];
        foreach ($allSubjects as $sub) {
            $r1 = $raw1[$sub->id] ?? null;
            $r2 = $raw2[$sub->id] ?? null;

            $rows[] = [
                'subject' => $sub->subject_name,
                'max1'    => $r1->max          ?? null,
                'pass1'   => $r1->passing      ?? null,
                'obt1'    => $r1->obtained     ?? null,
                'max2'    => $r2->max          ?? null,
                'pass2'   => $r2->passing      ?? null,
                'obt2'    => $r2->obtained     ?? null,
            ];
        }

        return response()->json(['rows' => $rows]);
    }


    /* =====================================================================
     |  YEAR-WISE EXAM ANALYSIS
     | ===================================================================== */
    public function yearWise(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $standards     = Standard::orderBy('display_order')->get();
        $divisions     = Division::orderBy('display_order')->get();

        $hasFilters = $request->filled(['academic_year_id', 'standard_id', 'division_id']);

        if (!$hasFilters) {
            return view('analytics.year-wise', compact(
                'academicYears', 'standards', 'divisions'
            ) + ['hasFilters' => false]);
        }

        $yearId     = (int) $request->academic_year_id;
        $standardId = (int) $request->standard_id;
        $divisionId = (int) $request->division_id;

        $yearModel    = AcademicYear::find($yearId);
        $standardName = Standard::find($standardId)->standard_name ?? '-';
        $divisionName = Division::find($divisionId)->division_name ?? '-';

        /* ---- Students ---- */
        $studentMap = collect();
        try {
            $erpStudents = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId);
            foreach ($erpStudents as $s) {
                $studentMap->put((string) $s->Studentid, [
                    'id'   => (string) $s->Studentid,
                    'name' => $s->full_name ?? trim(($s->studname ?? '') . ' ' . ($s->fathername ?? '')),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        if ($studentMap->isEmpty()) {
            return view('analytics.year-wise', compact(
                'academicYears', 'standards', 'divisions'
            ) + [
                'hasFilters' => true,
                'emptyMsg'   => 'No students found for the selected class.',
            ]);
        }

        $studentIds = $studentMap->keys()->all();

        /* ---- All exams for this year + standard ---- */
        $exams = ExamMaster::where('academic_year_id', $yearId)
            ->where('standard_id', $standardId)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($exams->isEmpty()) {
            return view('analytics.year-wise', compact(
                'academicYears', 'standards', 'divisions'
            ) + [
                'hasFilters' => true,
                'emptyMsg'   => 'No exams found for the selected Year + Standard.',
            ]);
        }

        /* ---- Per-exam KPI ---- */
        $examKPI = [];
        foreach ($exams as $exam) {
            $allowed = $this->allowedSubjectIds($exam->id, $standardId);

            $q = StudentMark::where('student_marks.exam_master_id', $exam->id)
                ->where('student_marks.standard_id', $standardId)
                ->where('student_marks.division_id', $divisionId)
                ->whereIn('student_marks.student_id', $studentIds)
                ->where('student_marks.is_optional', 0)
                ->join('subjects', 'subjects.id', '=', DB::raw('(' . $this->resolvedSubjectIdSql() . ')'))
                ->select(
                    'student_marks.student_id',
                    'subjects.id as subject_id',
                    'subjects.subject_name',
                    'student_marks.theory_obtained_marks',
                    'student_marks.oral_obtained_marks',
                    'student_marks.practical_obtained_marks',
                    'student_marks.theory_max_marks',
                    'student_marks.oral_max_marks',
                    'student_marks.practical_max_marks'
                );

            if (!empty($allowed)) {
                $q->whereIn('subjects.id', $allowed);
            }

            $marks = $q->get();

            $marks = $this->attachStructureMaxes($marks, $standardId, $exam->exam_name)
                ->unique(fn($r) => $r->student_id . '|' . $r->subject_id)
                ->values();

            $subjectStats = [];
            foreach ($marks->groupBy('subject_id') as $subId => $rows) {
                $subName = $rows->first()->subject_name;
                $subjectStats[$subId] = [
                    'subject_name' => $subName,
                    'sum'          => $rows->sum('obtained'),
                    'max'          => $rows->sum('max'),
                    'cnt'          => $rows->count(),
                    'pass_cnt'     => $rows->filter(fn($r) => $r->obtained >= $r->passing)->count(),
                ];
            }

            $studentPcts = [];
            foreach ($marks->groupBy('student_id') as $sid => $rows) {
                $obt = $rows->sum('obtained');
                $mx  = $rows->sum('max');
                if ($mx > 0) {
                    $studentPcts[(string) $sid] = round(($obt / $mx) * 100, 2);
                }
            }

            $cnt = count($studentPcts);
            $examKPI[$exam->id] = [
                'exam_name' => $exam->exam_name,
                'total'     => $cnt,
                'avg_pct'   => $cnt > 0 ? round(array_sum($studentPcts) / $cnt, 2) : 0,
                'highest'   => $cnt > 0 ? max($studentPcts) : 0,
                'lowest'    => $cnt > 0 ? min($studentPcts) : 0,
                'pass_pct'  => $cnt > 0
                    ? round((count(array_filter($studentPcts, fn($p) => $p >= 35)) / $cnt) * 100, 1)
                    : 0,
                'subject_stats' => $subjectStats,
                'student_pcts'  => $studentPcts,
            ];
        }

        /* ---- Subject × Exam matrix ---- */
        $subjectNames = [];
        foreach ($examKPI as $k) {
            foreach ($k['subject_stats'] as $sid => $info) {
                $subjectNames[$sid] = $info['subject_name'];
            }
        }
        ksort($subjectNames);

        $subjectExamMatrix = [];
        foreach ($subjectNames as $sid => $name) {
            $subjectExamMatrix[$sid] = [];
            foreach ($exams as $exam) {
                $stats = $examKPI[$exam->id]['subject_stats'][$sid] ?? null;
                if ($stats && $stats['cnt'] > 0 && $stats['max'] > 0) {
                    $subjectExamMatrix[$sid][$exam->id] =
                        round(($stats['sum'] / $stats['max']) * 100, 2);
                } else {
                    $subjectExamMatrix[$sid][$exam->id] = null;
                }
            }
        }

        /* ---- Student × Exam matrix ---- */
        $studentExamMatrix = [];
        foreach ($studentMap as $sid => $info) {
            $studentExamMatrix[$sid] = ['name' => $info['name'], 'exams' => []];
            foreach ($exams as $exam) {
                $studentExamMatrix[$sid]['exams'][$exam->id] =
                    $examKPI[$exam->id]['student_pcts'][$sid] ?? null;
            }
        }

        /* ---- Chart data ---- */
        $chartExamLabels  = $exams->pluck('exam_name')->all();
        $chartExamAvgPct  = collect($exams)->map(fn($e) => $examKPI[$e->id]['avg_pct']  ?? 0)->all();
        $chartExamPassPct = collect($exams)->map(fn($e) => $examKPI[$e->id]['pass_pct'] ?? 0)->all();

        return view('analytics.year-wise', compact(
            'academicYears', 'standards', 'divisions',
            'yearId', 'standardId', 'divisionId',
            'yearModel', 'standardName', 'divisionName',
            'exams', 'examKPI',
            'subjectNames', 'subjectExamMatrix',
            'studentExamMatrix',
            'chartExamLabels', 'chartExamAvgPct', 'chartExamPassPct'
        ) + ['hasFilters' => true]);
    }
}