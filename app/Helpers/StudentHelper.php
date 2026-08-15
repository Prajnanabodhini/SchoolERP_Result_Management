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
    | Find ERP Standard
    |--------------------------------------------------------------------------
    */

    $standardMap = DB::table('standard_year_mappings')
        ->where('academic_year_id', $yearId)
        ->where('standard_id', $laravelStandardId)
        ->first();

    if (!$standardMap || empty($standardMap->old_standard_id)) {
        return collect();
    }

    $oldStandardId = $standardMap->old_standard_id;


    /*
    |--------------------------------------------------------------------------
    | Find Local Division
    |--------------------------------------------------------------------------
    */

    $division = DB::table('divisions')
        ->where('id', $laravelDivisionId)
        ->where('is_active', 1)
        ->first();

    if (!$division) {
        return collect();
    }

    $divisionName = trim($division->division_name);


    /*
    |--------------------------------------------------------------------------
    | Find ERP Division
    |--------------------------------------------------------------------------
    */

    $erpDivision = DB::connection('sqlsrv_olderp')
        ->table('DivisionMst')
        ->where('yearid', $yearId)
        ->where('standardid', $oldStandardId)
        ->whereRaw(
            'UPPER(LTRIM(RTRIM(division))) = ?',
            [strtoupper($divisionName)]
        )
        ->first();

    if (!$erpDivision) {
        return collect();
    }

    $oldDivisionId = $erpDivision->divisionid;


    /*
    |--------------------------------------------------------------------------
    | Fetch Students
    |--------------------------------------------------------------------------
    */

    return DB::connection('sqlsrv_olderp')
        ->table('SubStudentMst as s')
        ->join(
            'FeeMstStudent as f',
            'f.Studentid',
            '=',
            's.Studentid'
        )
        ->select(
            's.Studentid',
            's.regno',
            's.rollno',
            's.standardid',
            's.divisionid',
            's.yearid',

            'f.studname',
            'f.fathername',
            'f.fathermobile',
            'f.gender',

            'f.birthdate',
            'f.admitdate',

            'f.adharno',
            'f.nationality',
            'f.reasonofleaving',
            'f.leavingdate',

            DB::raw("
                RTRIM(f.studname)
                +
                CASE
                    WHEN f.fathername IS NOT NULL
                    THEN ' ' + RTRIM(f.fathername)
                    ELSE ''
                END AS full_name
            ")
        )
        ->where('s.yearid', $yearId)
        ->where('s.standardid', $oldStandardId)
        ->where('s.divisionid', $oldDivisionId)
        ->orderByRaw('TRY_CAST(s.rollno AS INT)')
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
