<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StudentSkillMark;
use App\Models\StudentSkillSubject;
use App\Helpers\StudentHelper;

class StudentSkillMarkController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $exams         = ExamMaster::orderBy('exam_name')->get();
        $divisions     = Division::where('is_active', 1)->orderBy('division_name')->get();

        $skillSubjects = Subject::whereIn('subject_type_id', [2, 3])
            ->where('is_active', 1)
            ->orderBy('subject_name')
            ->get();

        $students     = collect();
        $maxMarks     = 100;
        $passingMarks = 35;
        $examStandard = null;

        if ($request->filled(['academic_year_id', 'exam_master_id', 'subject_id', 'division_id'])) {

            $academicYear = AcademicYear::find($request->academic_year_id);
            $examMaster   = ExamMaster::find($request->exam_master_id);

            if ($academicYear && $examMaster) {

                $examStandardId = $examMaster->standard_id;
                $examStandard   = Standard::find($examStandardId);

                // Auto-fetch max/passing from exam_master_subjects
                $examConfig = DB::table('exam_master_subjects')
                    ->where('exam_master_id', $examMaster->id)
                    ->where('subject_id', $request->subject_id)
                    ->first();

                // Cast to integer to avoid decimals
                $maxMarks     = (int) $request->input('max_marks', $examConfig->max_marks ?? 100);
                $passingMarks = (int) $request->input('passing_marks', $examConfig->passing_marks ?? 35);

                // Allocated student IDs for this skill subject
                $allocatedStudentIds = StudentSkillSubject::where('academic_year_id', $academicYear->id)
                    ->where('subject_id', $request->subject_id)
                    ->pluck('student_id')
                    ->toArray();

                if (!empty($allocatedStudentIds)) {

                    $yearId   = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);
                    $division = Division::find($request->division_id);

                    $classStudents = StudentHelper::getStudentsDirectERP(
                        $yearId,
                        $examStandardId,
                        $request->division_id
                    );

                    foreach ($classStudents as $s) {
                        if (in_array($s->Studentid, $allocatedStudentIds)) {
                            $s->class_label = ($examStandard->standard_name ?? '') . ' - ' . ($division->division_name ?? '');
                            $students->push($s);
                        }
                    }

                    if ($students->isNotEmpty()) {
                        $existingMarks = StudentSkillMark::where('academic_year_id', $academicYear->id)
                            ->where('exam_master_id', $examMaster->id)
                            ->where('subject_id', $request->subject_id)
                            ->whereIn('student_id', $allocatedStudentIds)
                            ->get()
                            ->keyBy('student_id');

                        foreach ($students as $student) {
                            $mark = $existingMarks[$student->Studentid] ?? null;
                            $student->marks_obtained = $mark && $mark->marks_obtained !== null
                                ? (int) $mark->marks_obtained
                                : null;
                            $student->grade          = $mark->grade ?? null;
                            $student->is_absent      = $mark->is_absent ?? 0;
                        }
                    }
                }
            }
        }

        return view('student-skill-marks.index', compact(
            'academicYears', 'exams', 'divisions', 'skillSubjects',
            'students', 'maxMarks', 'passingMarks', 'examStandard'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'exam_master_id'   => 'required',
            'subject_id'       => 'required',
        ]);

        $academicYearId = $request->academic_year_id;
        $examMasterId   = $request->exam_master_id;
        $subjectId      = $request->subject_id;
        $maxMarks       = (int) $request->input('max_marks', 100);
        $passingMarks   = (int) $request->input('passing_marks', 35);

        foreach ($request->marks ?? [] as $studentId => $markData) {

            $rawMarks      = $markData['obtained'] ?? null;
            $grade         = $markData['grade'] ?? null;
            $isAbsent      = !empty($markData['is_absent']) ? 1 : 0;

            // Cast marks to integer (strips decimals)
            $marksObtained = ($rawMarks !== null && $rawMarks !== '')
                ? (int) $rawMarks
                : null;

            // Skip if everything empty
            if (is_null($marksObtained) && is_null($grade) && !$isAbsent) {
                continue;
            }

            // If absent — force marks to 0 and grade to AB
            if ($isAbsent) {
                $marksObtained = 0;
                $grade         = 'AB';
                $percentage    = null;
            } else {
                $percentage = ($marksObtained !== null && $maxMarks > 0)
                    ? round(($marksObtained / $maxMarks) * 100, 2)
                    : null;

                // Auto-compute grade if not provided
                if (empty($grade) && $percentage !== null) {
                    $grade = $this->gradeFromPercentage($percentage);
                }
            }

            StudentSkillMark::updateOrCreate(
                [
                    'academic_year_id' => $academicYearId,
                    'exam_master_id'   => $examMasterId,
                    'student_id'       => $studentId,
                    'subject_id'       => $subjectId,
                ],
                [
                    'marks_obtained' => $marksObtained,
                    'max_marks'      => $maxMarks,
                    'passing_marks'  => $passingMarks,
                    'grade'          => $grade,
                    'is_absent'      => $isAbsent,
                    'percentage'     => $percentage,
                    'updated_by'     => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Skill Subject Marks Saved Successfully');
    }

    private function gradeFromPercentage($percentage): string
    {
        $p = (float) $percentage;
        if ($p >= 91) return 'A1';
        if ($p >= 81) return 'A2';
        if ($p >= 71) return 'B1';
        if ($p >= 61) return 'B2';
        if ($p >= 51) return 'C1';
        if ($p >= 41) return 'C2';
        if ($p >= 33) return 'D';
        if ($p >= 21) return 'E1';
        if ($p >= 1)  return 'E2';
        return 'F';
    }
}