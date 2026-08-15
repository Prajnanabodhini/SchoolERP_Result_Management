<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Section;
use App\Models\Division;
use App\Models\Standard;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\TeacherClassAllocation;

class TeacherClassAllocationController extends Controller
{
    public function index()
    {
        $allocations = TeacherClassAllocation::with([
    'teacher',
    'academicYear',
    'section',
    'standard',
    'division'
])
->latest()
->paginate(20)
->withQueryString();

        return view(
            'administrator.teacher-class-allocation.index',
            compact('allocations')
        );
    }

    public function create()
    {
        $teachers = User::where('role','Teacher')
            ->where('is_active',1)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('year_name')->get();

        $sections = Section::orderBy('display_order')->get();

        $standards = Standard::orderBy('display_order')->get();

        $divisions = Division::orderBy('display_order')->get();

        return view(
            'administrator.teacher-class-allocation.create',
            compact(
                'teachers',
                'academicYears',
                'sections',
                'standards',
                'divisions'
            )
        );
    }

    public function store(Request $request)
    {
        $exists = TeacherClassAllocation::where([
            'user_id' => $request->user_id,
            'academic_year_id' => $request->academic_year_id,
            'section_id' => $request->section_id,
            'standard_id' => $request->standard_id,
            'division_id' => $request->division_id
        ])->exists();

        if($exists){
            return back()
                ->with('error','Allocation already exists');
        }

        TeacherClassAllocation::create([
            'user_id' => $request->user_id,
            'academic_year_id' => $request->academic_year_id,
            'section_id' => $request->section_id,
            'standard_id' => $request->standard_id,
            'division_id' => $request->division_id,
            'is_class_teacher' => $request->has('is_class_teacher')
        ]);

        return redirect()
            ->route('teacher-class-allocation.index')
            ->with('success','Allocation Saved');
    }
}