<?php

namespace App\Http\Controllers\Marks;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\ExamMasterSubject;

class AdminMarksSubjectService
{
    /*
    |--------------------------------------------------------------------------
    | GET ACTUAL SUBJECT
    |--------------------------------------------------------------------------
    |
    | Supports both historical formats:
    |
    | CURRENT:
    |     stored ID = subjects.id
    |
    | LEGACY:
    |     stored ID = standard_wise_subjects.id
    |
    | Always returns the actual Subject Master record.
    |--------------------------------------------------------------------------
    */

    public function getActualSubject(
        $storedSubjectId,
        $standardId
    ) {
        $actualSubjectId =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId
            );

        if (!$actualSubjectId) {
            return null;
        }

        return Subject::query()
            ->where(
                'id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first([
                'id',
                'subject_name',
                'subject_code',
                'short_name',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    */

    public function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ) {
        $storedSubjectId =
            (int) $storedSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0
            ||
            $standardId <= 0
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        |
        | TSA.subject_id / TMS.subject_id
        |     =
        | subjects.id
        |
        |--------------------------------------------------------------------------
        */

        $currentMapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $storedSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first([
                'id',
                'subject_id',
            ]);

        if (
            $currentMapping
            &&
            $currentMapping->subject_id
        ) {

            return (int)
                $currentMapping->subject_id;
        }


        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        |
        | TSA.subject_id / TMS.subject_id
        |     =
        | standard_wise_subjects.id
        |
        |--------------------------------------------------------------------------
        */

        $legacyMapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'is_active',
                1
            )
            ->first([
                'id',
                'subject_id',
            ]);

        if (
            $legacyMapping
            &&
            $legacyMapping->subject_id
        ) {

            return (int)
                $legacyMapping->subject_id;
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL DIRECT SUBJECT FALLBACK
        |--------------------------------------------------------------------------
        |
        | Only accept it if that Subject is actually mapped to the
        | selected Standard.
        |--------------------------------------------------------------------------
        */

        $directSubjectExists =
            DB::table(
                'subjects'
            )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->exists();

        if (
            $directSubjectExists
        ) {

            $standardMappingExists =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->exists();

            if (
                $standardMappingExists
            ) {

                return $storedSubjectId;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT FROM MULTIPLE POSSIBLE IDS
    |--------------------------------------------------------------------------
    |
    | Useful when historical records may have:
    |
    | 1. student_marks.subject_id
    | 2. teacher_marks_status.subject_id
    | 3. teacher_subject_allocations.subject_id
    |
    |--------------------------------------------------------------------------
    */

    public function resolveFromCandidates(
        array $candidateIds,
        $standardId
    ) {
        $standardId =
            (int) $standardId;

        if (
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove invalid/duplicate IDs
        |--------------------------------------------------------------------------
        */

        $candidateIds =
            collect(
                $candidateIds
            )
            ->filter(
                function ($id) {
                    return (int) $id > 0;
                }
            )
            ->map(
                function ($id) {
                    return (int) $id;
                }
            )
            ->unique()
            ->values();


        foreach (
            $candidateIds as $candidateId
        ) {

            $subject =
                $this->getActualSubject(
                    $candidateId,
                    $standardId
                );

            if ($subject) {
                return $subject;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SUBJECT MAPPING
    |--------------------------------------------------------------------------
    */

    public function isMappedToStandard(
        $subjectId,
        $standardId
    ): bool {

        $subjectId =
            (int) $subjectId;

        $standardId =
            (int) $standardId;

        if (
            $subjectId <= 0
            ||
            $standardId <= 0
        ) {
            return false;
        }

        return DB::table(
            'standard_wise_subjects'
        )
        ->where(
            'standard_id',
            $standardId
        )
        ->where(
            'subject_id',
            $subjectId
        )
        ->where(
            'is_active',
            1
        )
        ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | GET STANDARD SUBJECTS
    |--------------------------------------------------------------------------
    |
    | This is the authoritative list of subjects for a Standard.
    |
    |--------------------------------------------------------------------------
    */

    public function getStandardSubjects(
        $standardId
    ): Collection {

        $standardId =
            (int) $standardId;

        if (
            $standardId <= 0
        ) {
            return collect();
        }

        return DB::table(
            'standard_wise_subjects as sws'
        )
        ->join(
            'subjects as s',
            's.id',
            '=',
            'sws.subject_id'
        )
        ->where(
            'sws.standard_id',
            $standardId
        )
        ->where(
            'sws.is_active',
            1
        )
        ->where(
            's.is_active',
            1
        )
        ->select([
            'sws.id as mapping_id',
            'sws.standard_id',
            'sws.subject_id',
            's.id as actual_subject_id',
            's.subject_name',
            's.subject_code',
            's.short_name',
            'sws.is_optional',
            'sws.sort_order',
        ])
        ->orderBy(
            'sws.sort_order'
        )
        ->orderBy(
            's.id'
        )
        ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | GET STANDARD SUBJECT MAP
    |--------------------------------------------------------------------------
    |
    | Returns two lookup maps:
    |
    | current:
    |     standard_id:subjects.id
    |
    | legacy:
    |     standard_id:standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    public function getStandardSubjectMaps(
        $standardIds
    ): array {

        $standardIds =
            collect(
                $standardIds
            )
            ->filter(
                function ($id) {
                    return (int) $id > 0;
                }
            )
            ->map(
                function ($id) {
                    return (int) $id;
                }
            )
            ->unique()
            ->values();

        $currentMap =
            [];

        $legacyMap =
            [];

        if (
            $standardIds->isEmpty()
        ) {
            return [
                'current' =>
                    $currentMap,

                'legacy' =>
                    $legacyMap,
            ];
        }


        $rows =
            DB::table(
                'standard_wise_subjects as sws'
            )
            ->join(
                'subjects as s',
                's.id',
                '=',
                'sws.subject_id'
            )
            ->whereIn(
                'sws.standard_id',
                $standardIds
            )
            ->where(
                'sws.is_active',
                1
            )
            ->where(
                's.is_active',
                1
            )
            ->select([
                'sws.id as mapping_id',
                'sws.standard_id',
                'sws.subject_id',
                's.id as actual_subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
                'sws.is_optional',
                'sws.sort_order',
            ])
            ->get();


        foreach (
            $rows as $row
        ) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT
            |--------------------------------------------------------------------------
            */

            $currentMap[
                (int) $row->standard_id
                . ':'
                . (int) $row->subject_id
            ] =
                $row;


            /*
            |--------------------------------------------------------------------------
            | LEGACY
            |--------------------------------------------------------------------------
            */

            $legacyMap[
                (int) $row->standard_id
                . ':'
                . (int) $row->mapping_id
            ] =
                $row;
        }


        return [
            'current' =>
                $currentMap,

            'legacy' =>
                $legacyMap,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    |
    | Supports:
    |
    | CURRENT:
    |     exam_master_subjects.subject_id = subjects.id
    |
    | LEGACY:
    |     exam_master_subjects.subject_id =
    |     standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    public function getSubjectConfig(
        $examId,
        $standardId,
        $subjectId
    ) {

        $examId =
            (int) $examId;

        $standardId =
            (int) $standardId;

        $subjectId =
            (int) $subjectId;

        if (
            $examId <= 0
            ||
            $standardId <= 0
            ||
            $subjectId <= 0
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        $config =
            ExamMasterSubject::query()
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $subjectId
                )
                ->first();

        if ($config) {
            return $config;
        }


        /*
        |--------------------------------------------------------------------------
        | LEGACY STANDARD-WISE SUBJECT IDs
        |--------------------------------------------------------------------------
        */

        $mappingIds =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->where(
                'is_active',
                1
            )
            ->pluck(
                'id'
            );


        if (
            $mappingIds->isEmpty()
        ) {
            return null;
        }


        return ExamMasterSubject::query()
            ->where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->whereIn(
                'subject_id',
                $mappingIds
            )
            ->orderBy(
                'id'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | GET ALL EXAM SUBJECT CONFIGURATIONS
    |--------------------------------------------------------------------------
    |
    | Useful for batch loading in the Entry/Assignment layer.
    |--------------------------------------------------------------------------
    */

    public function getExamSubjectConfigs(
        $examIds
    ): Collection {

        $examIds =
            collect(
                $examIds
            )
            ->filter(
                function ($id) {
                    return (int) $id > 0;
                }
            )
            ->map(
                function ($id) {
                    return (int) $id;
                }
            )
            ->unique()
            ->values();

        if (
            $examIds->isEmpty()
        ) {
            return collect();
        }

        return ExamMasterSubject::query()
            ->whereIn(
                'exam_master_id',
                $examIds
            )
            ->orderBy(
                'exam_master_id'
            )
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'id'
            )
            ->get([
                'id',
                'exam_master_id',
                'standard_id',
                'subject_id',
                'subject_name',
                'max_marks',
                'passing_marks',
                'display_order',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE EXAM SUBJECT
    |--------------------------------------------------------------------------
    |
    | Returns the actual ExamMasterSubject row after resolving current or
    | legacy subject IDs.
    |--------------------------------------------------------------------------
    */

    public function resolveExamSubject(
        $examId,
        $standardId,
        $storedSubjectId
    ) {

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId
            );

        if (!$actualSubjectId) {
            return null;
        }

        return $this->getSubjectConfig(
            $examId,
            $standardId,
            $actualSubjectId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECT DISPLAY DATA
    |--------------------------------------------------------------------------
    |
    | Returns a normalized object useful for Blade/API responses.
    |--------------------------------------------------------------------------
    */

    public function getSubjectDisplayData(
        $storedSubjectId,
        $standardId
    ) {

        $subject =
            $this->getActualSubject(
                $storedSubjectId,
                $standardId
            );

        if (!$subject) {
            return null;
        }

        return (object) [

            'subject_id' =>
                (int) $subject->id,

            'subject_name' =>
                $subject->subject_name
                ?? '-',

            'subject_code' =>
                $subject->subject_code
                ?? '',

            'short_name' =>
                $subject->short_name
                ?? '',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | GET POSSIBLE SUBJECT IDS
    |--------------------------------------------------------------------------
    |
    | Useful when dealing with historical data.
    |
    | Returns:
    |
    | subjects.id
    | +
    | standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    public function getPossibleSubjectIds(
        $actualSubjectId,
        $standardId
    ): Collection {

        $actualSubjectId =
            (int) $actualSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $actualSubjectId <= 0
            ||
            $standardId <= 0
        ) {
            return collect();
        }


        $ids =
            collect([
                $actualSubjectId,
            ]);


        $legacyIds =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->pluck(
                'id'
            );


        return $ids
            ->merge(
                $legacyIds
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->filter(
                fn ($id) => $id > 0
            )
            ->unique()
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE HISTORICAL SUBJECT
    |--------------------------------------------------------------------------
    |
    | Used when StudentMark contains a legacy subject ID.
    |--------------------------------------------------------------------------
    */

    public function resolveHistoricalSubject(
        $storedSubjectId,
        $standardId
    ) {

        return $this->getActualSubject(
            $storedSubjectId,
            $standardId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SAME LOGICAL SUBJECT
    |--------------------------------------------------------------------------
    |
    | Used by allocation/update code to determine whether two stored IDs
    | represent the same actual Subject Master.
    |--------------------------------------------------------------------------
    */

    public function representsSameSubject(
        $storedSubjectId,
        $actualSubjectId,
        $standardId
    ): bool {

        $resolved =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId
            );

        return
            $resolved !== null
            &&
            (int) $resolved ===
            (int) $actualSubjectId;
    }
}