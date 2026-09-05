<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\TeacherSubjectAllocation;

use App\Models\Standard;
use App\Models\Division;
use App\Models\StandardMst;
use App\Models\DivisionMst;
use App\Models\ErpStudentMaster;


class StudentHelper
{
    
public static function getERPStudents(
    $academicYear,
    $standardId,
    $divisionId
)
{

    $standard = DB::table('standards')
        ->where('id',$standardId)
        ->first();


    $division = DB::table('divisions')
        ->where('id',$divisionId)
        ->first();


    if(!$standard || !$division)
    {
        return collect();
    }


    $standardName = strtoupper(
        trim($standard->standard_name)
    );


    /*
    |--------------------------------------------------------------------------
    | Normalize Standard Name For ERP
    |--------------------------------------------------------------------------
    */

    $standardName = match($standardName)
    {

        'JR KG' => 'JRKG',

        'SR KG' => 'SR',

        'FOURTH' => 'FORTH',

        'NINTH' => 'NINETH',

        'ELEVENTH COMMERCE',
        'TWELFTH COMMERCE'
            => 'COMMERCE',

        'ELEVENTH SCIENCE',
        'TWELFTH SCIENCE'
            => 'SCIENCE',

        'ELEVENTH ARTS',
        'TWELFTH ARTS'
            => 'ARTS',

        default => $standardName

    };


    /*
    |--------------------------------------------------------------------------
    | Fetch ERP Students
    |--------------------------------------------------------------------------
    */

    return DB::table('erp_student_master')

        ->where(
            'academic_year_id',
            $academicYear
        )

        ->where(
            'standard_name',
            $standardName
        )

        ->where(
            'division_name',
            strtoupper(
                trim($division->division_name)
            )
        )

        ->select(
            '*',
            'gr_no as regno',
            'roll_no as rollno',
            'student_name as studname',
            'father_name as fathername',
            'mobile_no as fathermobile',
            'old_student_id as Studentid'
        )

        ->orderByRaw(
            'CAST(roll_no AS UNSIGNED)'
        )

        ->get();

}

public static function getStudentsForMarksEntry($yearId, $standardId, $divisionId)
    {
        $standard = \App\Models\Standard::find($standardId);
        $division = \App\Models\Division::find($divisionId);

        if (!$standard || !$division) {
            return collect();
        }

        /*
    |--------------------------------------------------------------------------
    | Find ERP Standard using old_standard_id
    |--------------------------------------------------------------------------
    */
        $erpStandard = DB::connection('sqlsrv_olderp')
            ->table('StandardMst')
            ->where('yearid', $yearId)
            ->where('standardid', $standard->old_standard_id)
            ->first();

        if (!$erpStandard) {
            return collect();
        }

        /*
    |--------------------------------------------------------------------------
    | Find ERP Division using Standard + Division Name
    |--------------------------------------------------------------------------
    */
        $erpDivision = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $yearId)
            ->where('standardid', $standard->old_standard_id)
            ->where('division', strtoupper(trim($division->division_name)))
            ->first();

        if (!$erpDivision) {
            return collect();
        }

        /*
    |--------------------------------------------------------------------------
    | Fetch Students
    |--------------------------------------------------------------------------
    */
        return DB::connection('sqlsrv_olderp')
            ->table('SubStudentMst as s')
            ->join('FeeMstStudent as f', 'f.Studentid', '=', 's.Studentid')
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                'f.studname',
                'f.fathername',
                DB::raw("RTRIM(f.studname) + ' ' + RTRIM(f.fathername) as full_name")
            )
            ->where('s.yearid', $yearId)
            ->where('s.standardid', $standard->old_standard_id)
            ->where('s.divisionid', $erpDivision->divisionid)
            ->orderBy('s.rollno')
            ->get();
    }

    public static function getStudentsForERP(
        $yearId,
        $standardId,
        $divisionId
    ) {
        $standard = Standard::find($standardId);

        $division = Division::find($divisionId);

        if (!$standard || !$division) {
            return collect();
        }

        $oldStandardId =
            getOldStandardId(
                $standard->standard_name
            );

        $standardMap =
            StandardMst::where('yearid', $yearId)
            ->where('standardid', $oldStandardId)
            ->first();

        if (!$standardMap) {
            return collect();
        }

        $divisionMap =
            DivisionMst::where('yearid', $yearId)
            ->where('standardid', $standardMap->standardid)
            ->where(
                'division',
                strtoupper($division->division_name)
            )
            ->first();

        if (!$divisionMap) {
            return collect();
        }

        return DB::table('SubStudentMst as s')
            ->join(
                'FeeMstStudent as f',
                'f.Studentid',
                '=',
                's.Studentid'
            )
            ->where('s.yearid', $yearId)
            ->where(
                's.standardid',
                $standardMap->standardid
            )
            ->where(
                's.divisionid',
                $divisionMap->divisionid
            )
            ->select(
                's.Studentid',
                's.rollno',
                's.regno',
                'f.studname'
            )
            ->orderBy('s.rollno')
            ->get();
    }

    public static function getStudents(
        $yearId,
        $sectionId,
        $standardId,
        $divisionId
    ) {
        return DB::table('SubStudentMst as s')
            ->join(
                'FeeMstStudent as f',
                'f.Studentid',
                '=',
                's.Studentid'
            )
            ->where('s.yearid', $yearId)
            ->where('s.sectionid', $sectionId)
            ->where('s.standardid', $standardId)
            ->where('s.divisionid', $divisionId)
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                DB::raw("
                    CONCAT(
                        f.studname,
                        ' ',
                        f.fathername
                    ) as studname
                ")
            )
            ->orderByRaw(
                'CAST(s.rollno AS UNSIGNED)'
            )
            ->get();
    }

    public static function getStudentsForTeacherAssignment($teacherSubjectAllocationId)
    {
        $tsa =
            \App\Models\TeacherSubjectAllocation::with([
                'allocation'
            ])
            ->find($teacherSubjectAllocationId);
    }

    public static function getStudentsOldERP(
        $yearId,
        $sectionId,
        $oldStandardId,
        $oldDivisionId
    ) {
        return DB::table('SubStudentMst as s')
            ->join(
                'FeeMstStudent as f',
                'f.Studentid',
                '=',
                's.Studentid'
            )
            ->where('s.yearid', $yearId)
            ->where('s.standardid', $oldStandardId)
            ->where('s.divisionid', $oldDivisionId)
            ->select(
                's.Studentid',
                's.regno',
                's.rollno',
                DB::raw("
                CONCAT(
                    f.studname,
                    ' ',
                    f.fathername
                ) as studname
            ")
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
        $standard =
            \App\Models\Standard::find(
                $laravelStandardId
            );

        $division =
            \App\Models\Division::find(
                $laravelDivisionId
            );

        if (!$standard || !$division) {
            return collect();
        }

        $oldStandardId =
            $standard->old_standard_id;

        $divisionMap =
            \App\Models\DivisionMst::where(
                'yearid',
                $yearId
            )
            ->where(
                'standardid',
                $oldStandardId
            )
            ->where(
                'division',
                strtoupper(
                    $division->division_name
                )
            )
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

    public static function getStudentsByStandardDivision(
        $laravelStandardId,
        $laravelDivisionId
    ) {
        $standard =
            \App\Models\Standard::find(
                $laravelStandardId
            );

        $division =
            \App\Models\Division::find(
                $laravelDivisionId
            );

        if (!$standard || !$division) {
            return collect();
        }

        $divisionMap =
            \App\Models\DivisionMst::where(
                'yearid',
                session('yearid')
            )
            ->where(
                'standardid',
                $standard->old_standard_id
            )
            ->where(
                'division',
                strtoupper(
                    $division->division_name
                )
            )
            ->first();

        if (!$divisionMap) {
            return collect();
        }

        return self::getStudents(
            session('yearid'),
            session('sectionid'),
            $standard->old_standard_id,
            $divisionMap->divisionid
        );
    }

 public static function getStudentsDirectERP(
    $yearId,
    $laravelStandardId,
    $laravelDivisionId
) {
    /*
    |--------------------------------------------------------------------------
    | 1. Get Laravel Standard
    |--------------------------------------------------------------------------
    */

    $standard = DB::table('standards')
        ->where('id', $laravelStandardId)
        ->where('is_active', 1)
        ->first();

    if (!$standard) {
        return collect();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Get Laravel Division
    |--------------------------------------------------------------------------
    */

    $division = DB::table('divisions')
        ->where('id', $laravelDivisionId)
        ->where('is_active', 1)
        ->first();

    if (!$division) {
        return collect();
    }

    $standardName = strtoupper(
        trim($standard->standard_name)
    );

    $divisionName = strtoupper(
        trim($division->division_name)
    );

    /*
    |--------------------------------------------------------------------------
    | 3. Map Laravel Standard Name to Old ERP Standard
    |--------------------------------------------------------------------------
    |
    | Normal standards use standard_year_mappings.
    |
    | XII Science  -> XIISCI -> 3163
    | XII Commerce -> XIICOM -> 3162
    |
    */

    $erpStandardId = null;

    /*
    |--------------------------------------------------------------------------
    | XII SCIENCE
    |--------------------------------------------------------------------------
    */

    if (
        in_array($standardName, [
            'TWELFTH SCIENCE',
            '12TH SCIENCE',
            '12 SCIENCE',
            'XII SCIENCE',
        ])
    ) {
        $erpStandard = DB::connection('sqlsrv_olderp')
            ->table('StandardMst')
            ->where('yearid', $yearId)
            ->where('standard', 'XIISCI')
            ->first();

        if ($erpStandard) {
            $erpStandardId = $erpStandard->standardid;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | XII COMMERCE
    |--------------------------------------------------------------------------
    */

    elseif (
        in_array($standardName, [
            'TWELFTH COMMERCE',
            '12TH COMMERCE',
            '12 COMMERCE',
            'XII COMMERCE',
        ])
    ) {
        $erpStandard = DB::connection('sqlsrv_olderp')
            ->table('StandardMst')
            ->where('yearid', $yearId)
            ->where('standard', 'XIICOM')
            ->first();

        if ($erpStandard) {
            $erpStandardId = $erpStandard->standardid;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Other Standards
    |--------------------------------------------------------------------------
    */

    else {

        $standardMap = DB::table('standard_year_mappings')
            ->where('academic_year_id', $yearId)
            ->where('standard_id', $laravelStandardId)
            ->first();

        if (
            $standardMap &&
            !empty($standardMap->old_standard_id)
        ) {
            $erpStandardId = $standardMap->old_standard_id;
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback to Standard.old_standard_id
        |--------------------------------------------------------------------------
        */

        if (!$erpStandardId && !empty($standard->old_standard_id)) {

            $erpStandardId = $standard->old_standard_id;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Stop if ERP Standard Not Found
    |--------------------------------------------------------------------------
    */

    if (!$erpStandardId) {
        return collect();
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Find ERP Division
    |--------------------------------------------------------------------------
    */

    $erpDivision = DB::connection('sqlsrv_olderp')
        ->table('DivisionMst')
        ->where('yearid', $yearId)
        ->where('standardid', $erpStandardId)
        ->whereRaw(
            'UPPER(LTRIM(RTRIM(division))) = ?',
            [$divisionName]
        )
        ->first();

    if (!$erpDivision) {
        return collect();
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Fetch Students
    |--------------------------------------------------------------------------
    |
    | This is the same SQL structure that you confirmed is working.
    |
    */

    return DB::connection('sqlsrv_olderp')
        ->table('SubStudentMst as ss')

        ->join(
            'FeeMstStudent as fs',
            'fs.Studentid',
            '=',
            'ss.Studentid'
        )

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
                +
                CASE
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

        ->where('ss.yearid', $yearId)

        ->where(
            'ss.standardid',
            $erpStandardId
        )

        ->where(
            'ss.divisionid',
            $erpDivision->divisionid
        )

        ->orderByRaw(
            'TRY_CAST(ss.rollno AS INT)'
        )

        ->get();
}

    public static function getERPDivisions($yearId, $laravelStandardId)
    {
        $standard = \App\Models\Standard::find(
            $laravelStandardId
        );




        if (!$standard) {
            return collect();
        }

        $erpStandardId = $standard->old_standard_id;

        return DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->where('yearid', $yearId)
            ->where('standardid', $erpStandardId)
            ->select(
                'divisionid',
                'division'
            )
            ->orderBy('division')
            ->get();
    }
}
