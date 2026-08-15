<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;


class StudentController extends Controller
{


    public function index(Request $request)
    {


        /*
    |--------------------------------------------------------------------------
    | Dropdown Data
    |--------------------------------------------------------------------------
    */


        $academicYears = AcademicYear::where('is_active', 1)
            ->orderByDesc('id')
            ->get();



        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();



        $divisions = Division::where('is_active', 1)
            ->orderBy('display_order')
            ->get();



        /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */


        $students = collect();



        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id')
        ) {

            $students = StudentHelper::getStudentsDirectERP(

                $request->academic_year_id,

                $request->standard_id,

                $request->division_id

            );

            $students = $students
                ->sort(function ($a, $b) {

                    $genderA = strtoupper(trim($a->gender ?? ''));
                    $genderB = strtoupper(trim($b->gender ?? ''));

                    // Girls first
                    if ($genderA !== $genderB) {

                        if ($genderA === 'FEMALE') {
                            return -1;
                        }

                        if ($genderB === 'FEMALE') {
                            return 1;
                        }
                    }

                    // Alphabetical within same gender
                    return strcmp(
                        strtoupper(trim($a->studname)),
                        strtoupper(trim($b->studname))
                    );
                })
                ->values();
        }



        $selectedYear = $request->academic_year_id;
        $selectedStandard = $request->standard_id;
        $selectedDivision = $request->division_id;


        return view(
            'students.index',
            compact(
                'academicYears',
                'standards',
                'divisions',
                'students',
                'selectedYear',
                'selectedStandard',
                'selectedDivision'
            )
        );
    }

    public function create()
    {
        return view('students.create');
    }



    public function store(Request $request)
    {

        Student::create([

            'admission_no' => $request->admission_no,

            'first_name' => $request->first_name,

            'last_name' => $request->last_name,

        ]);


        return redirect()
            ->route('students.index');
    }



    public function edit(Student $student)
    {
        return view(
            'students.edit',
            compact('student')
        );
    }



    public function update(Request $request, Student $student)
    {

        $student->update([

            'admission_no' => $request->admission_no,

            'first_name' => $request->first_name,

            'last_name' => $request->last_name,

        ]);


        return redirect()
            ->route('students.index');
    }



    public function destroy(Student $student)
    {

        $student->delete();


        return redirect()
            ->route('students.index');
    }
}
