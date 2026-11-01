<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherSubjectAllocation;

use App\Helpers\MarksHelper;

class MarkSaveController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SAVE MARKS (DRAFT)
    |--------------------------------------------------------------------------
    */

    public function save(Request $request)
    {
        $request->validate([
            'exam_master_id' => 'required|exists:exam_masters,id',
            'teacher_subject_allocation_id' => 'required|exists:teacher_subject_allocations,id',
            'student_ids' => 'required|array|min:1',
        ], [
            'exam_master_id.required' => 'Please select Exam.',
            'teacher_subject_allocation_id.required' => 'Please select Teaching Assignment.',
            'student_ids.required' => 'No students found.',
        ]);

        $teacherSubjectAllocation = TeacherSubjectAllocation::with([
            'allocation', 'allocation.standard', 'allocation.division',
            'allocation.teacher', 'allocation.academicYear', 'allocation.section',
            'subject', 'exam',
        ])->find($request->teacher_subject_allocation_id);

        if (!$teacherSubjectAllocation) {
            return back()->withInput()->with('error', 'Teaching Assignment not found.');
        }

        if ((int) $teacherSubjectAllocation->exam_master_id !== (int) $request->exam_master_id) {
            return back()->withInput()->with('error', 'Selected Exam does not match the Teaching Assignment.');
        }

        $allocation = $teacherSubjectAllocation->allocation;
        if (!$allocation) {
            return back()->withInput()->with('error', 'Teacher Class Allocation not found.');
        }

        $isOptionalEnabled = MarksHelper::isOptionalEnabledForAllocation($allocation);

        if (!MarksHelper::verifyTeacherAssignment($teacherSubjectAllocation, $request->exam_master_id)) {
            return back()->withInput()->with('error', 'You are not authorized to enter marks for this Teaching Assignment.');
        }

        $actualSubjectId = MarksHelper::resolveActualSubjectId($teacherSubjectAllocation->subject_id);
        if (!$actualSubjectId) {
            return back()->withInput()->with('error', 'Unable to find the Subject Master record for this Teaching Assignment.');
        }

        $actualSubject = Subject::where('id', $actualSubjectId)->where('is_active', 1)->first();
        if (!$actualSubject) {
            return back()->withInput()->with('error', 'Subject not found in Subject Master.');
        }

        $exam = ExamMaster::where('id', $request->exam_master_id)->where('is_active', 1)->first();
        if (!$exam) {
            return back()->withInput()->with('error', 'Exam Master not found.');
        }

        $yearError = MarksHelper::validateExamAcademicYear($exam, $allocation, $allocation->academic_year_id);
        if ($yearError) {
            return back()->withInput()->with('error', $yearError);
        }

        $isAdministrator = MarksHelper::isAdministrator();
        $marksStatus = MarksHelper::getTeacherMarksStatus($exam->id, $teacherSubjectAllocation->id, $isAdministrator, Auth::id());

        if (MarksHelper::isCompletedStatus($marksStatus)) {
            return back()->withInput()->with('error', 'Final submission already completed. Marks cannot be modified.');
        }

        $subjectConfig = MarksHelper::resolveExamSubjectConfig($exam, $allocation->standard_id, $actualSubjectId);
        if (!$subjectConfig) {
            return back()->withInput()->with('error', 'Marks configuration not found for ' . $actualSubject->subject_name . ' in ' . $exam->exam_name . '. Please configure this subject in Exam Master.');
        }

        if (MarksHelper::isTermExam($exam->exam_name ?? '')) {
            $components = MarksHelper::getTermAssessmentStructure($exam, $subjectConfig, $allocation->standard_id);
        } else {
            $components = MarksHelper::getComponentMaxMarks($exam, $subjectConfig);
        }

        $showTheory = (bool) ($components['show_theory'] ?? false);
        $showOral = (bool) ($components['show_oral'] ?? false);
        $showPractical = (bool) ($components['show_practical'] ?? false);

        $theoryMaxMarks = $components['theory_max'] ?? 0;
        $oralMaxMarks = $components['oral_max'] ?? 0;
        $practicalMaxMarks = $components['practical_max'] ?? 0;

        $theoryPassingMarks = $components['theory_passing'] ?? 0;
        $oralPassingMarks = $components['oral_passing'] ?? 0;
        $practicalPassingMarks = $components['practical_passing'] ?? 0;

        if ($showTheory && $theoryMaxMarks <= 0) {
            return back()->withInput()->with('error', 'Maximum Theory Marks is not configured for ' . $actualSubject->subject_name . '.');
        }
        if ($showOral && $oralMaxMarks <= 0) {
            return back()->withInput()->with('error', 'Maximum Oral Marks is not configured for ' . $actualSubject->subject_name . '.');
        }
        if ($showPractical && $practicalMaxMarks <= 0) {
            return back()->withInput()->with('error', 'Maximum Practical Marks is not configured for ' . $actualSubject->subject_name . '.');
        }

        foreach ($request->student_ids as $studentId) {
            $existingMark = StudentMark::where('academic_year_id', $allocation->academic_year_id)
                ->where('section_id', $allocation->section_id)
                ->where('student_id', $studentId)
                ->where('exam_master_id', $exam->id)
                ->where('subject_id', $actualSubjectId)
                ->first();

            if (!$existingMark) continue;

            $sameStandard = (int) $existingMark->standard_id === (int) $allocation->standard_id;
            $sameDivision = (int) $existingMark->division_id === (int) $allocation->division_id;

            if ($sameStandard && $sameDivision) {
                // ONLY block if it's locked AND belongs to the SAME teacher allocation
                if ((int) $existingMark->is_locked === 1 && (int)$existingMark->teacher_subject_allocation_id === (int)$teacherSubjectAllocation->id) {
                    return back()->withInput()->with('error', 'Marks for Student ID ' . $studentId . ' have already been submitted for ' . $actualSubject->subject_name . ' in ' . $exam->exam_name . '. Marks cannot be modified.');
                }
                continue;
            }

            $existingDivisionName = DB::table('divisions')->where('id', $existingMark->division_id)->value('division_name') ?: 'Division ID ' . $existingMark->division_id;
            $currentDivisionName = optional($allocation->division)->division_name ?: 'Division ID ' . $allocation->division_id;
            $existingStandardName = DB::table('standards')->where('id', $existingMark->standard_id)->value('standard_name') ?: 'Standard ID ' . $existingMark->standard_id;
            $currentStandardName = optional($allocation->standard)->standard_name ?: 'Standard ID ' . $allocation->standard_id;

            return back()->withInput()->with('error', 'Marks could not be saved for Student ID ' . $studentId . '. A mark record already exists for ' . $actualSubject->subject_name . ' in ' . $exam->exam_name . ' under ' . $existingStandardName . ' / ' . $existingDivisionName . '. You are trying to save marks under ' . $currentStandardName . ' / ' . $currentDivisionName . '. Please verify the selected Standard and Division.');
        }

        // ONLY check if marks locked by the current teacher
        $marksLocked = StudentMark::where('academic_year_id', $allocation->academic_year_id)
            ->where('section_id', $allocation->section_id)
            ->where('standard_id', $allocation->standard_id)
            ->where('division_id', $allocation->division_id)
            ->where('exam_master_id', $exam->id)
            ->where('subject_id', $actualSubjectId)
            ->where('teacher_subject_allocation_id', $teacherSubjectAllocation->id)
            ->where('is_locked', 1)
            ->exists();

        if ($marksLocked) {
            return back()->withInput()->with('error', 'Marks have already been submitted for this Exam, Subject and Division. Marks cannot be modified.');
        }

        DB::transaction(function () use (
            $request, $allocation, $teacherSubjectAllocation, $exam, $actualSubjectId,
            $theoryMaxMarks, $theoryPassingMarks, $oralMaxMarks, $oralPassingMarks,
            $practicalMaxMarks, $practicalPassingMarks, $showTheory, $showOral,
            $showPractical, $isOptionalEnabled
        ) {
            foreach ($request->student_ids as $studentId) {
                $values = MarksHelper::getOptionalMarkValues($request, $studentId, $isOptionalEnabled);
                $isOptional = $values['is_optional'];
                $isAbsent = $values['is_absent'];

                $theoryObtained = MarksHelper::resolveObtainedMark($request, 'theory_marks', $studentId, $isAbsent, $isOptional, $showTheory);
                $oralObtained = MarksHelper::resolveObtainedMark($request, 'oral_marks', $studentId, $isAbsent, $isOptional, $showOral);
                $practicalObtained = MarksHelper::resolveObtainedMark($request, 'practical_marks', $studentId, $isAbsent, $isOptional, $showPractical);

                $existingMark = StudentMark::where('academic_year_id', $allocation->academic_year_id)
                    ->where('section_id', $allocation->section_id)
                    ->where('student_id', $studentId)
                    ->where('exam_master_id', $exam->id)
                    ->where('subject_id', $actualSubjectId)
                    ->first();

                if ($existingMark) {
                    $existingMark->standard_id = $allocation->standard_id;
                    $existingMark->division_id = $allocation->division_id;
                    $existingMark->teacher_subject_allocation_id = $teacherSubjectAllocation->id; // Transfer ownership
                    $existingMark->theory_max_marks = $theoryMaxMarks;
                    $existingMark->theory_passing_marks = $theoryPassingMarks;
                    $existingMark->oral_max_marks = $oralMaxMarks;
                    $existingMark->oral_passing_marks = $oralPassingMarks;
                    $existingMark->practical_max_marks = $practicalMaxMarks;
                    $existingMark->practical_passing_marks = $practicalPassingMarks;
                    $existingMark->theory_obtained_marks = $theoryObtained;
                    $existingMark->oral_obtained_marks = $oralObtained;
                    $existingMark->practical_obtained_marks = $practicalObtained;
                    $existingMark->is_absent = $isAbsent ? 1 : 0;
                    $existingMark->is_optional = $isOptional ? 1 : 0;
                    $existingMark->is_locked = 0;
                    $existingMark->updated_by = Auth::id();
                    $existingMark->save();
                    continue;
                }

                StudentMark::create([
                    'academic_year_id' => $allocation->academic_year_id,
                    'section_id' => $allocation->section_id,
                    'standard_id' => $allocation->standard_id,
                    'division_id' => $allocation->division_id,
                    'student_id' => $studentId,
                    'exam_master_id' => $exam->id,
                    'subject_id' => $actualSubjectId,
                    'teacher_subject_allocation_id' => $teacherSubjectAllocation->id,
                    'theory_max_marks' => $theoryMaxMarks,
                    'theory_passing_marks' => $theoryPassingMarks,
                    'theory_obtained_marks' => $theoryObtained,
                    'oral_max_marks' => $oralMaxMarks,
                    'oral_passing_marks' => $oralPassingMarks,
                    'oral_obtained_marks' => $oralObtained,
                    'practical_max_marks' => $practicalMaxMarks,
                    'practical_passing_marks' => $practicalPassingMarks,
                    'practical_obtained_marks' => $practicalObtained,
                    'is_absent' => $isAbsent ? 1 : 0,
                    'is_optional' => $isOptional ? 1 : 0,
                    'is_locked' => 0,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('marks-entry.index', [
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id' => $request->exam_master_id,
            'teacher_subject_allocation_id' => $request->teacher_subject_allocation_id,
            'marks_saved' => 1,
        ])->with('success', 'Marks Saved Successfully (Draft).');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO SAVE
    |--------------------------------------------------------------------------
    */

    public function autoSave(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'exam_master_id' => 'required|exists:exam_masters,id',
            'teacher_subject_allocation_id' => 'required|exists:teacher_subject_allocations,id',
        ]);

        $teacherSubjectAllocation = TeacherSubjectAllocation::with([
            'allocation', 'allocation.standard', 'allocation.division', 'subject', 'exam'
        ])->find($request->teacher_subject_allocation_id);

        if (!$teacherSubjectAllocation) {
            return response()->json(['success' => false, 'message' => 'Teaching Assignment not found.'], 404);
        }

        if ((int) $teacherSubjectAllocation->exam_master_id !== (int) $request->exam_master_id) {
            return response()->json(['success' => false, 'message' => 'Exam does not match Teaching Assignment.'], 422);
        }

        $allocation = $teacherSubjectAllocation->allocation;
        if (!$allocation) {
            return response()->json(['success' => false, 'message' => 'Teacher Class Allocation not found.'], 422);
        }

        $isOptionalEnabled = MarksHelper::isOptionalEnabledForAllocation($allocation);

        if (!MarksHelper::verifyTeacherAssignment($teacherSubjectAllocation, $request->exam_master_id)) {
            return response()->json(['success' => false, 'message' => 'You are not authorized for this Teaching Assignment.'], 403);
        }

        $actualSubjectId = MarksHelper::resolveActualSubjectId($teacherSubjectAllocation->subject_id);
        if (!$actualSubjectId) {
            return response()->json(['success' => false, 'message' => 'Unable to resolve Subject ID.'], 422);
        }

        $exam = ExamMaster::where('id', $request->exam_master_id)->where('is_active', 1)->first();
        if (!$exam) {
            return response()->json(['success' => false, 'message' => 'Exam not found.'], 404);
        }

        $yearError = MarksHelper::validateExamAcademicYear($exam, $allocation, $allocation->academic_year_id);
        if ($yearError) {
            return response()->json(['success' => false, 'message' => $yearError], 422);
        }

        $isAdministrator = MarksHelper::isAdministrator();
        $marksStatus = MarksHelper::getTeacherMarksStatus($request->exam_master_id, $teacherSubjectAllocation->id, $isAdministrator, Auth::id());

        if (MarksHelper::isCompletedStatus($marksStatus)) {
            return response()->json(['success' => false, 'message' => 'Marks entry has already been completed.'], 422);
        }

        $subjectConfig = MarksHelper::resolveExamSubjectConfig($exam, $allocation->standard_id, $actualSubjectId);
        if (!$subjectConfig) {
            return response()->json(['success' => false, 'message' => 'Subject marks configuration not found.'], 422);
        }

        if (MarksHelper::isTermExam($exam->exam_name ?? '')) {
            $components = MarksHelper::getTermAssessmentStructure($exam, $subjectConfig, $allocation->standard_id);
        } else {
            $components = MarksHelper::getComponentMaxMarks($exam, $subjectConfig);
        }

        $theoryMaxMarks = $components['theory_max'] ?? 0;
        $oralMaxMarks = $components['oral_max'] ?? 0;
        $practicalMaxMarks = $components['practical_max'] ?? 0;
        $theoryPassingMarks = $components['theory_passing'] ?? 0;
        $oralPassingMarks = $components['oral_passing'] ?? 0;
        $practicalPassingMarks = $components['practical_passing'] ?? 0;

        $showTheory = (bool) ($components['show_theory'] ?? false);
        $showOral = (bool) ($components['show_oral'] ?? false);
        $showPractical = (bool) ($components['show_practical'] ?? false);

        DB::beginTransaction();
        try {
            $studentId = $request->student_id;

            $values = MarksHelper::getOptionalMarkValues($request, $studentId, $isOptionalEnabled);
            $isOptional = $values['is_optional'];
            $isAbsent = $values['is_absent'];

            $theoryObtained = MarksHelper::resolveObtainedMark($request, 'theory_marks', $studentId, $isAbsent, $isOptional, $showTheory);
            $oralObtained = MarksHelper::resolveObtainedMark($request, 'oral_marks', $studentId, $isAbsent, $isOptional, $showOral);
            $practicalObtained = MarksHelper::resolveObtainedMark($request, 'practical_marks', $studentId, $isAbsent, $isOptional, $showPractical);

            $mark = StudentMark::firstOrNew([
                'academic_year_id' => $allocation->academic_year_id,
                'section_id' => $allocation->section_id,
                'student_id' => $studentId,
                'exam_master_id' => $exam->id,
                'subject_id' => $actualSubjectId,
            ]);

            if ($mark->exists) {
                if ((int)$mark->standard_id !== (int)$allocation->standard_id || (int)$mark->division_id !== (int)$allocation->division_id) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'This student already has marks for this Exam and Subject under another Standard or Division.'], 422);
                }
                // 🔥 FIXED: ONLY BLOCK IF LOCKED BY THE SAME TEACHER ALLOCATION
                if ((int)$mark->is_locked === 1 && (int)$mark->teacher_subject_allocation_id === (int)$teacherSubjectAllocation->id) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Marks have already been submitted for this student.'], 422);
                }
            }

            $mark->standard_id = $allocation->standard_id;
            $mark->division_id = $allocation->division_id;
            $mark->teacher_subject_allocation_id = $teacherSubjectAllocation->id; // Transfer ownership

            $mark->theory_max_marks = $theoryMaxMarks;
            $mark->theory_passing_marks = $theoryPassingMarks;
            $mark->oral_max_marks = $oralMaxMarks;
            $mark->oral_passing_marks = $oralPassingMarks;
            $mark->practical_max_marks = $practicalMaxMarks;
            $mark->practical_passing_marks = $practicalPassingMarks;

            $mark->theory_obtained_marks = $theoryObtained;
            $mark->oral_obtained_marks = $oralObtained;
            $mark->practical_obtained_marks = $practicalObtained;

            $mark->is_absent = $isAbsent ? 1 : 0;
            $mark->is_optional = $isOptional ? 1 : 0;
            $mark->is_locked = 0; // Unlock it since new teacher is editing

            $mark->updated_by = Auth::id();
            if (!$mark->exists) {
                $mark->created_by = Auth::id();
            }

            $mark->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Auto-saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}