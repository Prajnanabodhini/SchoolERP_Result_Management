<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TeacherSubjectAllocation;

use App\Models\Standard;
use App\Models\Division;
use App\Models\StandardMst;
use App\Models\DivisionMst;
use App\Models\ErpStudentMaster;

class StudentHelper
{
    /**
     * Maps a Laravel standard name to the ERP StandardMst.standard code.
     * Returns null for standards that don't need a stream-specific code
     * (standards 1-10, pre-primary) — those are uniquely resolvable by
     * old_standard_id / standard_year_mappings.
     */
    private static function getErpStandardCode(?string $laravelStandardName): ?string
    {
        $name = strtoupper(trim((string) $laravelStandardName));

        return match ($name) {
            'ELEVENTH SCIENCE',
            '11TH SCIENCE',
            '11 SCIENCE',
            'XI SCIENCE'    => 'XISCI',

            'ELEVENTH COMMERCE',
            '11TH COMMERCE',
            '11 COMMERCE',
            'XI COMMERCE'   => 'XICOM',

            'ELEVENTH ARTS',
            '11TH ARTS',
            '11 ARTS',
            'XI ARTS'       => 'XIART',

            'TWELFTH SCIENCE',
            '12TH SCIENCE',
            '12 SCIENCE',
            'XII SCIENCE'   => 'XIISCI',

            'TWELFTH COMMERCE',
            '12TH COMMERCE',
            '12 COMMERCE',
            'XII COMMERCE'  => 'XIICOM',

            'TWELFTH ARTS',
            '12TH ARTS',
            '12 ARTS',
            'XII ARTS'      => 'XIIART',

            default         => null,
        };
    }

    /**
     * Resolve the ERP StandardMst.standardid for a given year + Laravel standard.
     *
     * Order of resolution:
     *   1. Stream-specific lookup via StandardMst.standard (XIART / XIISCI / …).
     *   2. standard_year_mappings (using $mappingAcademicYearId when supplied,
     *      otherwise $erpYearId for backward compatibility).
     *   3. standards.old_standard_id.
     */
    private static function resolveErpStandardIdForYear(
        string $erpYearId,
        int $laravelStandardId,
        ?int $mappingAcademicYearId = null
    ): ?int {
        $standard = DB::table('standards')
            ->where('id', $laravelStandardId)
            ->where('is_active', 1)
            ->first();

        if (!$standard) {
            return null;
        }

        $erpCode = self::getErpStandardCode($standard->standard_name);

        if ($erpCode !== null) {
            try {
                $row = DB::connection('sqlsrv_olderp')
                    ->table('StandardMst')
                    ->where('yearid', $erpYearId)
                    ->where('standard', $erpCode)
                    ->first();

                if ($row) {
                    return (int) $row->standardid;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        $lookupYearId = $mappingAcademicYearId ?? $erpYearId;

        $map = DB::table('standard_year_mappings')
            ->where('academic_year_id', $lookupYearId)
            ->where('standard_id', $laravelStandardId)
            ->first();

        if ($map && !empty($map->old_standard_id)) {
            return (int) $map->old_standard_id;
        }

        if (!empty($standard->old_standard_id)) {
            return (int) $standard->old_standard_id;
        }

        return null;
    }

    /**
     * Used by pages that read from the NEW ERP table (erp_student_master).
     * No change needed for 11th Arts — this table already stores stream names
     * like 'ARTS', 'SCIENCE', 'COMMERCE'.
     */
    public static function getERPStudents($academicYear, $standardId, $divisionId)
    {
        $standard = DB::table('standards')->where('id', $standardId)->first();
        $division = DB::table('divisions')->where('id', $divisionId)->first();

        if (!$standard || !$division) {
            return collect();
        }

        $standardName = strtoupper(trim($standard->standard_name));

        $standardName = match ($standardName) {
            'JR KG'  => 'JRKG',
            'SR KG'  => 'SR',
            'FOURTH' => 'FORTH',
            'NINTH'  => 'NINETH',
            'ELEVENTH COMMERCE',
            'TWELFTH COMMERCE' => 'COMMERCE',
            'ELEVENTH SCIENCE',
            'TWELFTH SCIENCE'  => 'SCIENCE',
            'ELEVENTH ARTS',
            'TWELFTH ARTS'     => 'ARTS',
            default            => $standardName
        };

        return DB::table('erp_student_master')
            ->where('academic_year_id', $academicYear)
            ->where('standard_name', $standardName)
            ->where('division_name', strtoupper(trim($division->division_name)))
            ->select(
                '*',
                'gr_no as regno',
                'roll_no as rollno',
                'student_name as studname',
                'father_name as fathername',
                'mobile_no as fathermobile',
                'old_student_id as Studentid'
            )
            ->orderByRaw('CAST(roll_no AS UNSIGNED)')
            ->get();
    }

    public static function getStudentsForMarksEntry($yearId, $standardId, $divisionId)
    {
        $standard = \App\Models\Standard::find($standardId);
        $division = \App\Models\Division::find($divisionId);

        if (!$standard || !$division) {
            return collect();
        }

        $erpStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $standardId);
        if (!$erpStandardId) {
            return collect();
        }

        $erpDivision = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $yearId)
            ->where('standardid', $erpStandardId)
            ->whereRaw('UPPER(LTRIM(RTRIM(division))) = ?', [strtoupper(trim($division->division_name))])
            ->first();

        if (!$erpDivision) {
            return collect();
        }

        return DB::connection('sqlsrv_olderp')
            ->table('SubStudentMst as s')
            ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
            ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'f.casteid')
            ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                'f.studname',
                'f.fathername',
                'f.casteid',
                'mc.Caste as caste',
                'cat.Categoryname as category_name',
                'f.gender',
                DB::raw("RTRIM(f.studname) + ' ' + RTRIM(f.fathername) as full_name")
            )
            ->where('s.yearid', $yearId)
            ->where('s.standardid', $erpStandardId)
            ->where('s.divisionid', $erpDivision->divisionid)
            ->orderBy('s.rollno')
            ->get();
    }

    public static function getStudentsForERP($yearId, $standardId, $divisionId)
    {
        $standard = Standard::find($standardId);
        $division = Division::find($divisionId);

        if (!$standard || !$division) {
            return collect();
        }

        $erpStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $standardId);
        if (!$erpStandardId) {
            return collect();
        }

        $divisionMap = DivisionMst::where('yearid', $yearId)
            ->where('standardid', $erpStandardId)
            ->where('division', strtoupper($division->division_name))
            ->first();

        if (!$divisionMap) {
            return collect();
        }

        return DB::table('SubStudentMst as s')
            ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
            ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'f.casteid')
            ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
            ->where('s.yearid', $yearId)
            ->where('s.standardid', $erpStandardId)
            ->where('s.divisionid', $divisionMap->divisionid)
            ->select(
                's.Studentid',
                's.rollno',
                's.regno',
                'f.studname',
                'f.gender',
                'f.casteid',
                'mc.Caste as caste',
                'cat.Categoryname as category_name'
            )
            ->orderBy('s.rollno')
            ->get();
    }

    public static function getStudents($yearId, $sectionId, $standardId, $divisionId)
    {
        return DB::table('SubStudentMst as s')
            ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
            ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'f.casteid')
            ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
            ->where('s.yearid', $yearId)
            ->where('s.sectionid', $sectionId)
            ->where('s.standardid', $standardId)
            ->where('s.divisionid', $divisionId)
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                'f.gender',
                'f.casteid',
                'mc.Caste as caste',
                'cat.Categoryname as category_name',
                DB::raw("CONCAT(f.studname, ' ', f.fathername) as studname")
            )
            ->orderByRaw('CAST(s.rollno AS UNSIGNED)')
            ->get();
    }

    public static function getStudentsForTeacherAssignment($teacherSubjectAllocationId)
    {
        $tsa = \App\Models\TeacherSubjectAllocation::with(['allocation'])
            ->find($teacherSubjectAllocationId);
    }

    public static function getStudentsOldERP($yearId, $sectionId, $oldStandardId, $oldDivisionId)
    {
        return DB::table('SubStudentMst as s')
            ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
            ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'f.casteid')
            ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
            ->where('s.yearid', $yearId)
            ->where('s.standardid', $oldStandardId)
            ->where('s.divisionid', $oldDivisionId)
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                'f.gender',
                'f.casteid',
                'mc.Caste as caste',
                'cat.Categoryname as category_name',
                DB::raw("CONCAT(f.studname, ' ', f.fathername) as studname")
            )
            ->orderByRaw('CAST(s.rollno AS UNSIGNED)')
            ->get();
    }

    public static function getStudentsByLaravelStandardDivision(
        $yearId,
        $sectionId,
        $laravelStandardId,
        $laravelDivisionId
    ) {
        $standard = \App\Models\Standard::find($laravelStandardId);
        $division = \App\Models\Division::find($laravelDivisionId);

        if (!$standard || !$division) {
            return collect();
        }

        $oldStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $laravelStandardId);
        if (!$oldStandardId) {
            return collect();
        }

        $divisionMap = \App\Models\DivisionMst::where('yearid', $yearId)
            ->where('standardid', $oldStandardId)
            ->whereRaw('UPPER(LTRIM(RTRIM(division))) = ?', [strtoupper(trim($division->division_name))])
            ->first();

        if (!$divisionMap) {
            return collect();
        }

        return self::getStudents(
            $yearId,
            $sectionId,
            $oldStandardId,
            $divisionMap->divisionid
        );
    }

    public static function getStudentsByStandardDivision($laravelStandardId, $laravelDivisionId)
    {
        $standard = \App\Models\Standard::find($laravelStandardId);
        $division = \App\Models\Division::find($laravelDivisionId);

        if (!$standard || !$division) {
            return collect();
        }

        $yearId = session('yearid');
        $oldStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $laravelStandardId);
        if (!$oldStandardId) {
            return collect();
        }

        $divisionMap = \App\Models\DivisionMst::where('yearid', $yearId)
            ->where('standardid', $oldStandardId)
            ->whereRaw('UPPER(LTRIM(RTRIM(division))) = ?', [strtoupper(trim($division->division_name))])
            ->first();

        if (!$divisionMap) {
            return collect();
        }

        return self::getStudents(
            $yearId,
            session('sectionid'),
            $oldStandardId,
            $divisionMap->divisionid
        );
    }

    public static function getStudentsDirectERP($yearId, $laravelStandardId, $laravelDivisionId)
    {
        $inputYearId = trim((string) $yearId);
        $academicYear = DB::table('academic_years')
            ->where('id', $yearId)
            ->first();

        if (!$academicYear && $inputYearId !== '') {
            $academicYear = DB::table('academic_years')
                ->where('old_year_id', $inputYearId)
                ->first();
        }

        if (!$academicYear && preg_match('/^\d{4}/', $inputYearId, $yearMatch)) {
            $academicYear = DB::table('academic_years')
                ->where('year_name', 'like', $yearMatch[0] . '%')
                ->orderBy('id')
                ->first();
        }

        $mappingAcademicYearId = $academicYear->id ?? $yearId;
        $erpYearId = $academicYear
            ? (trim((string) ($academicYear->old_year_id ?? ''))
                ?: substr(trim((string) ($academicYear->year_name ?? '')), 0, 4))
            : $yearId;

        if ($erpYearId === null || trim((string) $erpYearId) === '') {
            Log::warning('StudentHelper::getStudentsDirectERP could not resolve the selected academic year.', [
                'input_year_id' => $yearId,
                'standard_id' => $laravelStandardId,
                'division_id' => $laravelDivisionId,
            ]);

            return collect();
        }

        $standard = DB::table('standards')
            ->where('id', $laravelStandardId)
            ->where('is_active', 1)
            ->first();

        if (!$standard) {
            return collect();
        }

        $division = DB::table('divisions')
            ->where('id', $laravelDivisionId)
            ->where('is_active', 1)
            ->first();

        if (!$division) {
            return collect();
        }

        $standardName = strtoupper(trim($standard->standard_name));
        $divisionName = strtoupper(trim($division->division_name));

        $erpStandardId = self::resolveErpStandardIdForYear(
            (string) $erpYearId,
            (int) $laravelStandardId,
            $mappingAcademicYearId ? (int) $mappingAcademicYearId : null
        );

        if (!$erpStandardId) {
            Log::warning('StudentHelper::getStudentsDirectERP could not resolve the ERP standard.', [
                'erp_year_id' => $erpYearId,
                'standard_id' => $laravelStandardId,
                'standard_name' => $standardName,
            ]);

            return collect();
        }

        $erpDivision = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $erpYearId)
            ->where('standardid', $erpStandardId)
            ->whereRaw('UPPER(LTRIM(RTRIM(division))) = ?', [$divisionName])
            ->first();

        if (!$erpDivision) {
            Log::warning('StudentHelper::getStudentsDirectERP could not resolve the ERP division.', [
                'erp_year_id' => $erpYearId,
                'erp_standard_id' => $erpStandardId,
                'division_id' => $laravelDivisionId,
                'division_name' => $divisionName,
            ]);

            return collect();
        }

        $rows = DB::connection('sqlsrv_olderp')
            ->table('SubStudentMst as ss')
            ->join('FeeMstStudent as fs', 'fs.Studentid', '=', 'ss.Studentid')
            ->select(
                'ss.Studentid',
                'ss.regno',
                'fs.studname',
                'fs.fathername',
                'ss.standardid',
                'ss.divisionid',
                'ss.rollno',
                'ss.yearid',
                'ss.sectionid',
                DB::raw("
                    RTRIM(fs.studname)
                    + CASE
                        WHEN fs.fathername IS NOT NULL
                             AND LTRIM(RTRIM(fs.fathername)) <> ''
                        THEN ' ' + RTRIM(fs.fathername)
                        ELSE ''
                    END AS full_name
                "),
                'fs.fathermobile',
                'fs.gender',
                'fs.birthdate',
                'fs.admitdate',
                'fs.adharno',
                'fs.nationality',
                'fs.reasonofleaving',
                'fs.leavingdate'
            )
            ->where('ss.yearid', $erpYearId)
            ->where('ss.standardid', $erpStandardId)
            ->where('ss.divisionid', $erpDivision->divisionid)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $students = $rows
            ->sortBy(function ($row) {
                $roll = trim((string) ($row->rollno ?? ''));

                return $roll !== '' && is_numeric($roll)
                    ? [0, (int) $roll, (string) $row->Studentid]
                    : [1, strtoupper($roll), (string) $row->Studentid];
            })
            ->filter(fn ($row) => trim((string) ($row->Studentid ?? '')) !== '')
            ->unique(fn ($row) => (string) $row->Studentid)
            ->values();

        $studentIds = $students->pluck('Studentid')->filter()->unique()->values()->all();
        if (!empty($studentIds)) {
            $casteMap = DB::connection('sqlsrv_olderp')
                ->table('FeeMstStudent as fs')
                ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'fs.casteid')
                ->leftJoin('MstSubCaste as msc', 'msc.subcasteid', '=', 'fs.subcasteid')
                ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
                ->whereIn('fs.Studentid', $studentIds)
                ->select(
                    'fs.Studentid',
                    DB::raw('mc.Caste AS caste'),
                    DB::raw('msc.subcaste AS subcaste'),
                    DB::raw('cat.Categoryname AS category_name')
                )
                ->get()
                ->keyBy('Studentid');

            foreach ($students as $student) {
                $row = $casteMap->get($student->Studentid);
                $student->caste         = $row->caste         ?? null;
                $student->subcaste      = $row->subcaste      ?? null;
                $student->category_name = $row->category_name ?? null;
            }
        }

        return $students;
    }

    public static function getERPDivisions($yearId, $laravelStandardId)
    {
        $standard = \App\Models\Standard::find($laravelStandardId);

        if (!$standard) {
            return collect();
        }

        $erpStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $laravelStandardId);
        if (!$erpStandardId) {
            return collect();
        }

        return DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $yearId)
            ->where('standardid', $erpStandardId)
            ->select('divisionid', 'division')
            ->orderBy('division')
            ->get();
    }

    public static function getStudentsDirectERPWithCaste($yearId, $laravelStandardId, $laravelDivisionId)
    {
        return self::getStudentsDirectERP($yearId, $laravelStandardId, $laravelDivisionId);
    }

    public static function getERPDivisionsForStandard($academicYearId, $laravelStandardId): \Illuminate\Support\Collection
    {
        $academicYearId    = (int) $academicYearId;
        $laravelStandardId = (int) $laravelStandardId;

        if ($academicYearId <= 0 || $laravelStandardId <= 0) {
            return collect();
        }

        $yearName = (string) DB::table('academic_years')
            ->where('id', $academicYearId)
            ->value('year_name');

        $yearId = substr($yearName, 0, 4);
        if ($yearId === '' || !ctype_digit($yearId)) {
            return collect();
        }

        $erpStandardId = self::resolveErpStandardIdForYear(
            $yearId,
            $laravelStandardId,
            $academicYearId
        );

        if (!$erpStandardId) {
            return collect();
        }

        try {
            $erpDivisionIds = DB::connection('sqlsrv_olderp')
                ->table('SubStudentMst')
                ->where('yearid', $yearId)
                ->where('standardid', $erpStandardId)
                ->distinct()
                ->pluck('divisionid')
                ->map(fn ($v) => (int) $v)
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return collect();
        }

        if (empty($erpDivisionIds)) {
            return collect();
        }

        try {
            $divNames = DB::connection('sqlsrv_olderp')
                ->table('DivisionMst')
                ->where('yearid', $yearId)
                ->where('standardid', $erpStandardId)
                ->whereIn('divisionid', $erpDivisionIds)
                ->pluck('division', 'divisionid')
                ->toArray();
        } catch (\Throwable $e) {
            return collect();
        }

        if (empty($divNames)) {
            return collect();
        }

        $normalizedNames = [];
        foreach ($divNames as $name) {
            $clean = strtoupper(trim((string) $name));
            if ($clean !== '') {
                $normalizedNames[] = $clean;
            }
        }

        if (empty($normalizedNames)) {
            return collect();
        }

        $validIds = DB::table('divisions')
            ->where('is_active', 1)
            ->whereIn(DB::raw('UPPER(TRIM(division_name))'), $normalizedNames)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        if (empty($validIds)) {
            return collect();
        }

        return DB::table('divisions')
            ->whereIn('id', $validIds)
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get(['id', 'division_name']);
    }

    public static function getERPDivisionsByStandard($academicYearId): array
    {
        $academicYearId = (int) $academicYearId;
        if ($academicYearId <= 0) {
            return [];
        }

        $yearName = (string) DB::table('academic_years')
            ->where('id', $academicYearId)
            ->value('year_name');

        $yearId = substr($yearName, 0, 4);
        if ($yearId === '' || !ctype_digit($yearId)) {
            return [];
        }

        try {
            $rows = DB::connection('sqlsrv_olderp')
                ->table('SubStudentMst')
                ->where('yearid', $yearId)
                ->select('standardid', 'divisionid')
                ->distinct()
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        if ($rows->isEmpty()) {
            return [];
        }

        $erpDivisionIds = $rows->pluck('divisionid')
            ->unique()
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->all();

        try {
            $divNames = DB::connection('sqlsrv_olderp')
                ->table('DivisionMst')
                ->where('yearid', $yearId)
                ->whereIn('divisionid', $erpDivisionIds)
                ->pluck('division', 'divisionid')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }

        $laravelStandards = DB::table('standards')
            ->where('is_active', 1)
            ->get(['id', 'standard_name', 'old_standard_id']);

        $mappings = DB::table('standard_year_mappings')
            ->where('academic_year_id', $academicYearId)   // fix: use Laravel ID, not 4-digit year
            ->pluck('old_standard_id', 'standard_id')
            ->toArray();

        $laravelDivisions = DB::table('divisions')
            ->where('is_active', 1)
            ->get(['id', 'division_name']);

        $divNameToId = [];
        foreach ($laravelDivisions as $d) {
            $divNameToId[strtoupper(trim((string) $d->division_name))] = (int) $d->id;
        }

        // Pre-fetch ALL stream codes in one query (11th + 12th streams).
        $specialCodes = ['XISCI', 'XICOM', 'XIART', 'XIISCI', 'XIICOM', 'XIIART'];
        try {
            $specialStandards = DB::connection('sqlsrv_olderp')
                ->table('StandardMst')
                ->where('yearid', $yearId)
                ->whereIn('standard', $specialCodes)
                ->pluck('standardid', 'standard')
                ->toArray();
        } catch (\Throwable $e) {
            $specialStandards = [];
        }

        $erpToLaravelStdMap = [];
        foreach ($laravelStandards as $ls) {
            $stdName = strtoupper(trim((string) $ls->standard_name));
            $lsId    = (int) $ls->id;
            $erpId   = null;

            $erpCode = self::getErpStandardCode($stdName);
            if ($erpCode !== null) {
                $erpId = $specialStandards[$erpCode] ?? null;
            }

            if (!$erpId) {
                $erpId = $mappings[$lsId] ?? $ls->old_standard_id ?? null;
            }

            if ($erpId) {
                $erpToLaravelStdMap[(int) $erpId] = $lsId;
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $laravelStdId = $erpToLaravelStdMap[(int) $row->standardid] ?? null;
            if (!$laravelStdId) continue;

            $erpDivName = $divNames[(int) $row->divisionid] ?? null;
            if (!$erpDivName) continue;

            $laravelDivId = $divNameToId[strtoupper(trim($erpDivName))] ?? null;
            if (!$laravelDivId) continue;

            $out[$laravelStdId][$laravelDivId] = true;
        }

        $allDivIds = [];
        foreach ($out as $divIds) {
            foreach (array_keys($divIds) as $id) {
                $allDivIds[$id] = true;
            }
        }
        $allDivIds = array_keys($allDivIds);

        $divisionRows = empty($allDivIds)
            ? collect()
            : DB::table('divisions')
                ->whereIn('id', $allDivIds)
                ->where('is_active', 1)
                ->orderBy('division_name')
                ->get(['id', 'division_name'])
                ->keyBy('id');

        $final = [];
        foreach ($out as $stdId => $divIds) {
            $collection = collect();
            foreach (array_keys($divIds) as $divId) {
                if (isset($divisionRows[$divId])) {
                    $collection->push($divisionRows[$divId]);
                }
            }
            $final[$stdId] = $collection->sortBy('division_name')->values();
        }

        return $final;
    }

    public static function getStudentPersonalInfo($studentId, $yearId, $sectionId = null)
    {
        if (!$studentId || !$yearId) {
            return null;
        }

        try {
            $query = DB::connection('sqlsrv_olderp')
                ->table('SubStudentMst as s')
                ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
                ->leftJoin('StandardMst as std', function ($join) {
                    $join->on('std.standardid', '=', 's.standardid')
                         ->on('std.yearid',     '=', 's.yearid');
                })
                ->leftJoin('DivisionMst as div', function ($join) {
                    $join->on('div.divisionid', '=', 's.divisionid')
                         ->on('div.standardid', '=', 's.standardid')
                         ->on('div.yearid',     '=', 's.yearid');
                })
                ->leftJoin('studmast as sm', 'sm.regno', '=', 's.regno')
                ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'f.casteid')
                ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
                ->where('s.Studentid', $studentId)
                ->where('s.yearid', $yearId);

            if ($sectionId !== null && $sectionId !== '') {
                $query->where('s.sectionid', $sectionId);
            }

            $row = $query->select(
                'f.Studentid            as internal_id',
                'f.saralid              as gov_student_id',
                'f.studid               as alt_student_id',
                'f.adharno              as aadhar_no',
                's.regno                as gr_no',
                's.rollno               as roll_no',
                's.yearid               as year_id',
                's.sectionid            as section_id',
                'f.studname             as name',
                'f.fathername           as father_name',
                'f.mothername           as mother_name',
                'f.guardname            as guardian_name',
                'f.relation             as guardian_relation',
                'f.gender               as gender',
                'f.birthdate            as dob',
                'f.birthplace           as birth_place',
                'f.mtounge              as mother_tongue',
                'f.religion             as religion',
                'f.nationality          as nationality',
                'f.taluka               as taluka',
                'f.district             as district',
                'f.location             as location',
                'f.locaddr              as local_address',
                'f.peraddr              as permanent_address',
                'sm.laddress            as legacy_address',
                'sm.taluka              as legacy_taluka',
                'sm.district            as legacy_district',
                'f.fathermobile         as father_mobile',
                'f.mothermobile         as mother_mobile',
                'f.resphone1            as residence_phone',
                'mc.Caste               as caste',
                'cat.Categoryname       as category_name',
                DB::raw('std.standard AS standard_name'),
                DB::raw('div.division AS division_name')
            )
            ->first();

            return $row ?: null;

        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function resolveStudentAddress($student): string
    {
        if (!$student) return '';

        $parts = array_filter([
            $student->permanent_address ?? null,
            $student->local_address     ?? null,
            $student->legacy_address    ?? null,
            $student->location          ?? null,
            $student->taluka            ?? null,
            $student->district          ?? null,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        return implode(', ', array_unique($parts));
    }

    public static function getStudentsDirectERPSlim($yearId, $laravelStandardId, $laravelDivisionId)
    {
        $standard = DB::table('standards')
            ->where('id', $laravelStandardId)
            ->where('is_active', 1)
            ->first();

        if (!$standard) return collect();

        $division = DB::table('divisions')
            ->where('id', $laravelDivisionId)
            ->where('is_active', 1)
            ->first();

        if (!$division) return collect();

        $divisionName = strtoupper(trim($division->division_name));

        $erpStandardId = self::resolveErpStandardIdForYear((string) $yearId, (int) $laravelStandardId);
        if (!$erpStandardId) return collect();

        $erpDivision = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $yearId)
            ->where('standardid', $erpStandardId)
            ->whereRaw('UPPER(LTRIM(RTRIM(division))) = ?', [$divisionName])
            ->first();

        if (!$erpDivision) return collect();

        $students = DB::connection('sqlsrv_olderp')
            ->table('SubStudentMst as ss')
            ->join('FeeMstStudent as fs', 'fs.Studentid', '=', 'ss.Studentid')
            ->select(
                'ss.Studentid as Studentid',
                'ss.regno     as regno',
                'ss.rollno    as rollno',
                'fs.studname  as studname',
                'fs.fathername as fathername',
                'fs.gender    as gender'
            )
            ->where('ss.yearid', $yearId)
            ->where('ss.standardid', $erpStandardId)
            ->where('ss.divisionid', $erpDivision->divisionid)
            ->get();

        if ($students->isEmpty()) {
            return $students;
        }

        $studentIds = $students->pluck('Studentid')->filter()->unique()->values()->all();

        if (!empty($studentIds)) {
            $casteMap = DB::connection('sqlsrv_olderp')
                ->table('FeeMstStudent as fs')
                ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'fs.casteid')
                ->leftJoin('MstSubCaste as msc', 'msc.subcasteid', '=', 'fs.subcasteid')
                ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
                ->whereIn('fs.Studentid', $studentIds)
                ->select(
                    'fs.Studentid',
                    DB::raw('mc.Caste AS caste'),
                    DB::raw('msc.subcaste AS subcaste'),
                    DB::raw('cat.Categoryname AS category_name')
                )
                ->get()
                ->keyBy('Studentid');

            foreach ($students as $student) {
                $row = $casteMap->get($student->Studentid);
                $student->caste         = $row->caste         ?? null;
                $student->subcaste      = $row->subcaste      ?? null;
                $student->category_name = $row->category_name ?? null;
            }
        }

        return $students;
    }
}