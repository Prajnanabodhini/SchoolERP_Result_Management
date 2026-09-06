<?php

namespace App\Helpers;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;
use App\Models\Section;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherBulkHelper
{
    public static function teachers(): Collection
    {
        return \App\Models\User::where('role', 'Teacher')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
    }

    public static function academicYears(): Collection
    {
        return AcademicYear::where('is_active', 1)
            ->orderByDesc('id')
            ->get();
    }

    public static function sections(): Collection
    {
        return Section::orderBy('display_order')->get();
    }

    public static function standards(): Collection
    {
        return Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();
    }

    public static function divisions(): Collection
    {
        return Division::where('is_active', 1)
            ->orderBy('display_order')
            ->get();
    }

    public static function exams($academicYearId = null, $standardId = null): Collection
    {
        $query = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('exam_name');

        if ($academicYearId !== null && $academicYearId !== '') {
            $query->where('academic_year_id', (int) $academicYearId);
        }

        if ($standardId !== null && $standardId !== '') {
            $query->where('standard_id', (int) $standardId);
        }

        return $query->get();
    }

    public static function resolveLegacySubjectId($storedSubjectId, $standardId): int
    {
        $storedSubjectId = (int) $storedSubjectId;
        $standardId = (int) $standardId;

        if ($storedSubjectId <= 0 || $standardId <= 0) {
            return 0;
        }

        if (Subject::where('id', $storedSubjectId)->where('is_active', 1)->exists()) {
            return $storedSubjectId;
        }

        $mapping = DB::table('standard_wise_subjects')
            ->where('id', $storedSubjectId)
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->first();

        return ($mapping && $mapping->subject_id) ? (int) $mapping->subject_id : 0;
    }

    public static function resolveSubject($incomingSubjectId, $standardId, $examMasterId): ?array
{
    $incomingSubjectId = (int) $incomingSubjectId;
    $standardId = (int) $standardId;
    $examMasterId = (int) $examMasterId;

    if (
        $incomingSubjectId <= 0 ||
        $standardId <= 0 ||
        $examMasterId <= 0
    ) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | EXAM
    |--------------------------------------------------------------------------
    */

    $exam = ExamMaster::find($examMasterId);

    if (!$exam) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | STANDARD MUST MATCH EXAM
    |--------------------------------------------------------------------------
    */

    if (
        $exam->standard_id &&
        (int) $exam->standard_id !== $standardId
    ) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | 1. TRY STANDARD-WISE MAPPING BY MASTER SUBJECT ID
    |--------------------------------------------------------------------------
    */

    $standardSubject = DB::table('standard_wise_subjects as sws')
        ->where('sws.standard_id', $standardId)
        ->where('sws.subject_id', $incomingSubjectId)
        ->where('sws.is_active', 1)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | 2. TRY LEGACY STANDARD-WISE MAPPING ID
    |--------------------------------------------------------------------------
    */

    if (!$standardSubject) {
        $standardSubject = DB::table('standard_wise_subjects as sws')
            ->where('sws.standard_id', $standardId)
            ->where('sws.id', $incomingSubjectId)
            ->where('sws.is_active', 1)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. DIRECT MASTER SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | Blade submits subjects.id.
    | Therefore Subject ID 10 is valid when subjects.id = 10
    | and that subject is attached to the selected Exam.
    |
    */

    $subject = null;

    if ($standardSubject) {

        $subject = Subject::where('id', $standardSubject->subject_id)
            ->where('is_active', 1)
            ->first();

    } else {

        /*
        |--------------------------------------------------------------------------
        | DIRECT SUBJECT MASTER FALLBACK
        |--------------------------------------------------------------------------
        */

        $subject = Subject::where('id', $incomingSubjectId)
            ->where('is_active', 1)
            ->first();

        if (!$subject) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE A STANDARD-WISE OBJECT ONLY FOR RESOLUTION
        |--------------------------------------------------------------------------
        |
        | We do not write anything to database here.
        |
        */

        $standardSubject = DB::table('standard_wise_subjects as sws')
            ->where('sws.standard_id', $standardId)
            ->where('sws.subject_id', $subject->id)
            ->where('sws.is_active', 1)
            ->first();
    }

    if (!$subject) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | EXAM SUBJECT MUST CONTAIN THE SAME MASTER SUBJECT
    |--------------------------------------------------------------------------
    */

    $examSubject = ExamMasterSubject::where('exam_master_id', $examMasterId)
        ->where('subject_id', $subject->id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | LEGACY EXAM MAPPING SUPPORT
    |--------------------------------------------------------------------------
    */

    if (!$examSubject && $standardSubject) {

        $examSubject = ExamMasterSubject::where(
                'exam_master_id',
                $examMasterId
            )
            ->where(
                'subject_id',
                $standardSubject->id
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT IS NOT PART OF SELECTED EXAM
    |--------------------------------------------------------------------------
    */

    if (!$examSubject) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN RESOLVED DATA
    |--------------------------------------------------------------------------
    */

    return [
        'subject' => $subject,
        'examSubject' => $examSubject,
        'standardSubject' => $standardSubject,
    ];
}

    public static function getExamMappedSubjects($examMasterId, $standardId = null): Collection
    {
        $exam = ExamMaster::find($examMasterId);

        if (!$exam) {
            return collect();
        }

        $standardId = $standardId ? (int) $standardId : (int) $exam->standard_id;

        if (!$standardId) {
            return collect();
        }

        if ($exam->standard_id && (int) $exam->standard_id !== $standardId) {
            return collect();
        }

        $examSubjects = ExamMasterSubject::where('exam_master_id', $examMasterId)
            ->whereNotNull('subject_id')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get([
                'id', 'exam_master_id', 'standard_id', 'subject_id',
                'subject_name', 'max_marks', 'passing_marks', 'display_order',
            ]);

        if ($examSubjects->isEmpty()) {
            return collect();
        }

        $standardMappings = DB::table('standard_wise_subjects as sws')
            ->leftJoin('standards as st', 'st.id', '=', 'sws.standard_id')
            ->leftJoin('subjects as s', 's.id', '=', 'sws.subject_id')
            ->where('sws.standard_id', $standardId)
            ->where('sws.is_active', 1)
            ->where('s.is_active', 1)
            ->select([
                'sws.id as mapping_id', 'sws.standard_id', 'sws.subject_id',
                'st.standard_name', 's.subject_name', 's.subject_code',
                's.short_name', 'sws.is_optional', 'sws.sort_order',
            ])
            ->orderBy('sws.sort_order')
            ->orderBy('sws.id')
            ->get();

        $standardName = optional(Standard::find($standardId))->standard_name ?? '';
        $subjects = collect();

        foreach ($examSubjects as $examSubject) {
            $storedSubjectId = (int) $examSubject->subject_id;
            if ($storedSubjectId <= 0) {
                continue;
            }

            $mappedSubject = $standardMappings->first(
                fn ($mapping) => (int) $mapping->subject_id === $storedSubjectId
            );

            if (!$mappedSubject) {
                $mappedSubject = $standardMappings->first(
                    fn ($mapping) => (int) $mapping->mapping_id === $storedSubjectId
                );
            }

            if (!$mappedSubject) {
                $directSubject = Subject::where('id', $storedSubjectId)
                    ->where('is_active', 1)
                    ->first();

                if ($directSubject) {
                    $subjects->push((object) [
                        'mapping_id' => null,
                        'standard_id' => $standardId,
                        'subject_id' => (int) $directSubject->id,
                        'standard_name' => $standardName,
                        'subject_name' => $directSubject->subject_name ?: '-',
                        'subject_code' => $directSubject->subject_code ?: '',
                        'short_name' => $directSubject->short_name ?: '',
                        'is_optional' => 0,
                        'sort_order' => (int) ($examSubject->display_order ?? 9999),
                        'exam_subject_id' => $examSubject->id,
                        'max_marks' => $examSubject->max_marks,
                        'passing_marks' => $examSubject->passing_marks,
                        'display_order' => (int) ($examSubject->display_order ?? 9999),
                    ]);
                }
                continue;
            }

            $subjects->push((object) [
                'mapping_id' => (int) $mappedSubject->mapping_id,
                'standard_id' => (int) $mappedSubject->standard_id,
                'subject_id' => (int) $mappedSubject->subject_id,
                'standard_name' => $mappedSubject->standard_name ?: $standardName,
                'subject_name' => $mappedSubject->subject_name ?: ($examSubject->subject_name ?: '-'),
                'subject_code' => $mappedSubject->subject_code ?: '',
                'short_name' => $mappedSubject->short_name ?: '',
                'is_optional' => (int) ($mappedSubject->is_optional ?? 0),
                'sort_order' => (int) ($mappedSubject->sort_order ?? 9999),
                'exam_subject_id' => $examSubject->id,
                'max_marks' => $examSubject->max_marks,
                'passing_marks' => $examSubject->passing_marks,
                'display_order' => (int) ($examSubject->display_order ?? $mappedSubject->sort_order ?? 9999),
            ]);
        }

        return $subjects
            ->unique('subject_id')
            ->sortBy([
                ['display_order', 'asc'],
                ['sort_order', 'asc'],
                ['subject_id', 'asc'],
            ])
            ->values();
    }

    public static function prepareIndexAllocations($allocations): void
    {
        $tsaIds = $allocations->flatMap(
            fn ($allocation) => $allocation->subjectAllocations->pluck('id')
        )->filter()->unique()->values();

        $statuses = collect();
        if ($tsaIds->isNotEmpty()) {
            $statuses = DB::table('teacher_marks_status as tms')
                ->whereIn('tms.teacher_subject_allocation_id', $tsaIds)
                ->select([
                    'tms.teacher_subject_allocation_id', 'tms.academic_year_id',
                    'tms.standard_id', 'tms.division_id', 'tms.subject_id',
                    'tms.exam_master_id', 'tms.teacher_id', 'tms.status',
                ])
                ->get()
                ->keyBy('teacher_subject_allocation_id');
        }

        $standardIds = $allocations->pluck('standard_id')->filter()->unique()
            ->map(fn ($id) => (int) $id)->values();

        $standardSubjects = collect();
        if ($standardIds->isNotEmpty()) {
            $standardSubjects = DB::table('standard_wise_subjects as sws')
                ->leftJoin('standards as st', 'st.id', '=', 'sws.standard_id')
                ->leftJoin('subjects as s', 's.id', '=', 'sws.subject_id')
                ->whereIn('sws.standard_id', $standardIds)
                ->where('sws.is_active', 1)
                ->where('s.is_active', 1)
                ->select([
                    'sws.id as mapping_id', 'sws.standard_id', 'sws.subject_id',
                    'st.standard_name', 's.subject_name', 's.subject_code',
                    's.short_name', 'sws.is_optional', 'sws.sort_order',
                ])
                ->orderBy('sws.standard_id')->orderBy('sws.sort_order')->orderBy('sws.id')
                ->get()->groupBy('standard_id');
        }

        foreach ($allocations as $allocation) {
            $allocation->displaySubjects = collect();
            $currentMappings = $standardSubjects->get((int) $allocation->standard_id, collect());

            foreach ($allocation->subjectAllocations as $tsa) {
                $status = $statuses->get($tsa->id);
                $examId = (int) ($tsa->exam_master_id ?? $status?->exam_master_id ?? 0);
                $tsaSubjectId = (int) ($tsa->subject_id ?? 0);
                $statusSubjectId = (int) ($status?->subject_id ?? 0);

                $candidates = collect([
                    self::resolveLegacySubjectId($statusSubjectId, (int) $allocation->standard_id),
                    self::resolveLegacySubjectId($tsaSubjectId, (int) $allocation->standard_id),
                    $statusSubjectId,
                    $tsaSubjectId,
                ])->filter(fn ($id) => (int) $id > 0)->map(fn ($id) => (int) $id)->unique();

                $mapped = null;
                $examSubject = null;
                $examSubjects = $examId > 0 ? ExamMasterSubject::where('exam_master_id', $examId)->get() : collect();

                foreach ($candidates as $candidate) {
                    $mapped = $currentMappings->first(fn ($mapping) => (int) $mapping->subject_id === $candidate);
                    if ($mapped) break;
                }
                foreach ($candidates as $candidate) {
                    $examSubject = $examSubjects->first(fn ($row) => (int) $row->subject_id === $candidate);
                    if ($examSubject) break;
                }
                if (!$mapped) {
                    foreach ($candidates as $candidate) {
                        $mapped = $currentMappings->first(fn ($mapping) => (int) $mapping->mapping_id === $candidate);
                        if ($mapped) break;
                    }
                }

                $subjectId = 0;
                $subjectName = '-';
                $subjectCode = '';
                $shortName = '';
                $sortOrder = 9999;

                if ($mapped) {
                    $subjectId = (int) $mapped->subject_id;
                    $subjectName = $mapped->subject_name ?: '-';
                    $subjectCode = $mapped->subject_code ?: '';
                    $shortName = $mapped->short_name ?: '';
                    $sortOrder = (int) ($mapped->sort_order ?? 9999);
                } elseif ($examSubject) {
                    $direct = Subject::where('id', (int) $examSubject->subject_id)->where('is_active', 1)->first();
                    if ($direct) {
                        $subjectId = (int) $direct->id;
                        $subjectName = $direct->subject_name ?: '-';
                        $subjectCode = $direct->subject_code ?: '';
                        $shortName = $direct->short_name ?: '';
                    } else {
                        $subjectId = (int) $examSubject->subject_id;
                        $subjectName = $examSubject->subject_name ?: '-';
                        $sortOrder = (int) ($examSubject->display_order ?? 9999);
                    }
                } else {
                    $subjectId = self::resolveLegacySubjectId($tsaSubjectId, (int) $allocation->standard_id);
                    $subject = Subject::where('id', $subjectId)->where('is_active', 1)->first();
                    if ($subject) {
                        $subjectName = $subject->subject_name ?: '-';
                        $subjectCode = $subject->subject_code ?: '';
                        $shortName = $subject->short_name ?: '';
                    }
                }

                $allocation->displaySubjects->push((object) [
                    'teacher_subject_allocation_id' => $tsa->id,
                    'subject_id' => $subjectId,
                    'stored_subject_id' => $tsaSubjectId,
                    'subject_name' => $subjectName,
                    'subject_code' => $subjectCode,
                    'short_name' => $shortName,
                    'sort_order' => $sortOrder,
                    'status' => $status?->status ?? 'PENDING',
                    'exam_master_id' => $examId,
                ]);
            }

            $allocation->displaySubjects = $allocation->displaySubjects
                ->unique('teacher_subject_allocation_id')
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['subject_name', 'asc'],
                ])->values();
        }
    }

    /* Exam is authoritative; the request academic_year_id is intentionally ignored. */
    public static function validateExamYear($exam, $academicYearId = null): ?string
    {
        if (!$exam->academic_year_id) {
            return 'Selected Exam does not have an Academic Year assigned.';
        }

        return null;
    }

    public static function validateExamStandard($exam, $standard): ?string
    {
        if ($exam->standard_id && (int) $exam->standard_id !== (int) $standard->id) {
            return 'Selected Exam does not belong to selected Standard.';
        }

        if (!$standard->section_id) {
            return 'Section is not assigned to selected Standard.';
        }

        return null;
    }

    public static function createTeacherMarksStatus($tsa, $request, $exam, $standard, $division, $subject)
    {
        return TeacherMarksStatus::create([
            'academic_year_id' => (int) $exam->academic_year_id,
            'exam_master_id' => $exam->id,
            'teacher_subject_allocation_id' => $tsa->id,
            'standard_id' => $standard->id,
            'division_id' => $division->id,
            'subject_id' => $subject->id,
            'teacher_id' => $request->user_id,
            'status' => 'PENDING',
        ]);
    }

    public static function ensureTeacherMarksStatus($tsa, $request, $exam, $standard, $division, $subject)
    {
        $status = TeacherMarksStatus::where('teacher_subject_allocation_id', $tsa->id)->first();
        if ($status) return $status;

        return self::createTeacherMarksStatus($tsa, $request, $exam, $standard, $division, $subject);
    }

    public static function tsaRepresentsSubject($tsa, $actualSubjectId, $standardId): bool
    {
        $storedId = (int) $tsa->subject_id;
        $actualSubjectId = (int) $actualSubjectId;
        $standardId = (int) $standardId;

        if ($storedId <= 0 || $actualSubjectId <= 0) return false;
        if ($storedId === $actualSubjectId) return true;

        $mapping = DB::table('standard_wise_subjects')
            ->where('id', $storedId)
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->first();

        return $mapping && (int) $mapping->subject_id === $actualSubjectId;
    }

    public static function store(Request $request): void
    {
        $exam = ExamMaster::findOrFail($request->exam_master_id);

        if (!$exam->academic_year_id) {
            throw new \Exception('Selected Exam does not have an Academic Year assigned.');
        }

        /* IMPORTANT: Exam determines the Academic Year. */
        $request->merge([
            'academic_year_id' => (int) $exam->academic_year_id,
        ]);

        foreach ($request->rows as $row) {
            foreach ($row['standards'] as $standardId) {
                $standard = Standard::findOrFail($standardId);
                $standardError = self::validateExamStandard($exam, $standard);
                if ($standardError) throw new \Exception($standardError);

                foreach ($row['divisions'] as $divisionId) {
                    $division = Division::findOrFail($divisionId);

                    $allocation = TeacherClassAllocation::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'academic_year_id' => (int) $exam->academic_year_id,
                            'section_id' => $standard->section_id,
                            'standard_id' => $standard->id,
                            'division_id' => $division->id,
                        ],
                        [
                            'is_class_teacher' => !empty($row['is_class_teacher']),
                        ]
                    );

                    self::assertAllocationYear($allocation, $exam);

                    foreach ($row['subjects'] as $subjectId) {
                        $resolved = self::resolveSubject($subjectId, $standard->id, $exam->id);
                        if (!$resolved) {
                            throw new \Exception("Subject ID {$subjectId} is not valid for {$standard->standard_name} and selected Exam.");
                        }

                        $subject = $resolved['subject'];

                        $existing = TeacherSubjectAllocation::where('teacher_class_allocation_id', $allocation->id)
                            ->where('exam_master_id', $exam->id)
                            ->get()
                            ->first(fn ($tsa) => self::tsaRepresentsSubject($tsa, $subject->id, $standard->id));

                        if ($existing) {
                            self::ensureTeacherMarksStatus($existing, $request, $exam, $standard, $division, $subject);
                            continue;
                        }

                        $tsa = TeacherSubjectAllocation::create([
                            'teacher_class_allocation_id' => $allocation->id,
                            'subject_id' => $subject->id,
                            'exam_master_id' => $exam->id,
                        ]);

                        self::createTeacherMarksStatus($tsa, $request, $exam, $standard, $division, $subject);
                    }
                }
            }
        }
    }

    public static function assertAllocationYear($allocation, $exam): void
    {
        if ((int) $allocation->academic_year_id !== (int) $exam->academic_year_id) {
            throw new \Exception('Teacher Class Allocation Academic Year does not match the selected Exam Academic Year.');
        }
    }

    public static function buildEditData($id): array
    {
        $allocation = TeacherClassAllocation::findOrFail($id);
        $standard = Standard::findOrFail($allocation->standard_id);

        $exams = self::exams($allocation->academic_year_id, $allocation->standard_id);
        $existing = TeacherSubjectAllocation::where('teacher_class_allocation_id', $allocation->id)->orderBy('id')->get();
        $selectedExamId = $existing->pluck('exam_master_id')->filter()->last();

        if ($selectedExamId) {
            $selectedExam = ExamMaster::find($selectedExamId);
            if (!$selectedExam || (int) $selectedExam->academic_year_id !== (int) $allocation->academic_year_id) {
                $selectedExamId = null;
            }
        }

        $subjects = collect();
        if ($selectedExamId) {
            $subjects = self::getExamMappedSubjects($selectedExamId, $allocation->standard_id);
        }

        $selectedSubjects = [];
        if ($selectedExamId) {
            $existing->where('exam_master_id', $selectedExamId)->each(function ($tsa) use (&$selectedSubjects, $allocation) {
                $id = self::resolveLegacySubjectId($tsa->subject_id, $allocation->standard_id);
                if ($id > 0) $selectedSubjects[] = $id;
            });
        }

        $selectedSubjects = collect($selectedSubjects)->unique()->values()->toArray();

        if (!$selectedExamId || $subjects->isEmpty()) {
            $subjects = DB::table('standard_wise_subjects as sws')
                ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.standard_id', $allocation->standard_id)
                ->where('sws.is_active', 1)
                ->where('s.is_active', 1)
                ->select([
                    's.id as subject_id', 's.subject_name', 's.subject_code',
                    's.short_name', 'sws.is_optional', 'sws.sort_order',
                ])
                ->orderBy('sws.sort_order')->orderBy('s.id')->get();
        }

        $subjects = collect($subjects)->map(function ($subject) {
            return (object) [
                'subject_id' => (int) ($subject->subject_id ?? $subject->id ?? 0),
                'subject_name' => $subject->subject_name ?? '-',
                'subject_code' => $subject->subject_code ?? '',
                'short_name' => $subject->short_name ?? '',
                'is_optional' => (int) ($subject->is_optional ?? 0),
                'sort_order' => (int) ($subject->sort_order ?? $subject->display_order ?? 9999),
            ];
        })->filter(fn ($subject) => $subject->subject_id > 0)
            ->unique('subject_id')
            ->sortBy([
                ['sort_order', 'asc'],
                ['subject_name', 'asc'],
            ])->values();

        return [
            'allocation' => $allocation,
            'standard' => $standard,
            'exams' => $exams,
            'subjects' => $subjects,
            'selectedSubjects' => $selectedSubjects,
            'selectedExamId' => $selectedExamId,
        ];
    }

    public static function update(Request $request, $id): void
    {
        $allocation = TeacherClassAllocation::findOrFail($id);
        $exam = ExamMaster::findOrFail($request->exam_master_id);
        $standard = Standard::findOrFail($request->standard_id);

        if (!$exam->academic_year_id) {
            throw new \Exception('Selected Exam does not have an Academic Year assigned.');
        }

        /* IMPORTANT: Exam determines the Academic Year. */
        $request->merge([
            'academic_year_id' => (int) $exam->academic_year_id,
        ]);

        $standardError = self::validateExamStandard($exam, $standard);
        if ($standardError) throw new \Exception($standardError);

        $selectedSubjectIds = collect($request->input('subjects', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()->values();

        if ($selectedSubjectIds->isEmpty()) {
            throw new \Exception('Please select at least one Subject.');
        }

        $resolvedSubjects = collect();
        foreach ($selectedSubjectIds as $subjectId) {
            $resolved = self::resolveSubject($subjectId, $standard->id, $exam->id);
            if (!$resolved) {
                throw new \Exception("Subject ID {$subjectId} is not valid for {$standard->standard_name} and selected Exam.");
            }
            $resolvedSubjects->put((int) $resolved['subject']->id, $resolved);
        }

        $division = Division::findOrFail($request->division_id);

        DB::transaction(function () use (
            $request, $allocation, $exam, $standard, $division,
            $selectedSubjectIds, $resolvedSubjects
        ) {
            $existingCount = TeacherSubjectAllocation::where('teacher_class_allocation_id', $allocation->id)->count();

            if ($existingCount > 0) {
                $target = TeacherClassAllocation::where('user_id', $request->user_id)
                    ->where('academic_year_id', (int) $exam->academic_year_id)
                    ->where('section_id', $standard->section_id)
                    ->where('standard_id', $standard->id)
                    ->where('division_id', $request->division_id)
                    ->first();

                if (!$target) {
                    $target = TeacherClassAllocation::create([
                        'user_id' => $request->user_id,
                        'academic_year_id' => (int) $exam->academic_year_id,
                        'section_id' => $standard->section_id,
                        'standard_id' => $standard->id,
                        'division_id' => $request->division_id,
                        'is_class_teacher' => $allocation->is_class_teacher ?? 0,
                    ]);
                }
            } else {
                $allocation->update([
                    'user_id' => $request->user_id,
                    'academic_year_id' => (int) $exam->academic_year_id,
                    'section_id' => $standard->section_id,
                    'standard_id' => $standard->id,
                    'division_id' => $request->division_id,
                ]);
                $target = $allocation;
            }

            self::assertAllocationYear($target, $exam);

            $existing = TeacherSubjectAllocation::where('teacher_class_allocation_id', $target->id)
                ->where('exam_master_id', $exam->id)->get();

            foreach ($selectedSubjectIds as $selectedSubjectId) {
                $resolved = $resolvedSubjects->get((int) $selectedSubjectId);
                if (!$resolved) continue;

                $subject = $resolved['subject'];
                $existingTsa = $existing->first(fn ($tsa) => self::tsaRepresentsSubject($tsa, $subject->id, $standard->id));

                if ($existingTsa) {
                    self::ensureTeacherMarksStatus($existingTsa, $request, $exam, $standard, $division, $subject);
                    continue;
                }

                $tsa = TeacherSubjectAllocation::create([
                    'teacher_class_allocation_id' => $target->id,
                    'subject_id' => $subject->id,
                    'exam_master_id' => $exam->id,
                ]);

                self::createTeacherMarksStatus($tsa, $request, $exam, $standard, $division, $subject);
            }
        });
    }

    public static function destroy($id): void
    {
        $allocation = TeacherClassAllocation::findOrFail($id);
        $tsaIds = TeacherSubjectAllocation::where('teacher_class_allocation_id', $allocation->id)->pluck('id');

        if ($tsaIds->isNotEmpty()) {
            $hasMarks = DB::table('student_marks')
                ->whereIn('teacher_subject_allocation_id', $tsaIds)
                ->exists();

            if ($hasMarks) {
                throw new \Exception('This allocation cannot be deleted because examination marks already exist. Existing marks and allocations are protected.');
            }
        }

        DB::transaction(function () use ($allocation, $tsaIds) {
            if ($tsaIds->isNotEmpty()) {
                TeacherMarksStatus::whereIn('teacher_subject_allocation_id', $tsaIds)->delete();
                TeacherSubjectAllocation::whereIn('id', $tsaIds)->delete();
            }
            $allocation->delete();
        });
    }
}
