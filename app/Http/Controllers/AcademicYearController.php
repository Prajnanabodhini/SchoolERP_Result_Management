<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderByDesc('id')->get();

        return view('academic-years.index', compact('years'));
    }

    public function create()
    {
        return view('academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_name' => 'required'
        ]);

        AcademicYear::create([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_current' => $request->has('is_current'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year Added Successfully');
    }

    public function edit(AcademicYear $academic_year)
    {
        return view('academic-years.edit', compact('academic_year'));
    }

    public function update(Request $request, AcademicYear $academic_year)
    {
        $academic_year->update([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_current' => $request->has('is_current'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year Updated Successfully');
    }

    public function destroy(AcademicYear $academic_year)
    {
        $academic_year->delete();

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year Deleted Successfully');
    }
}