<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::orderBy('display_order')->get();

        return view('sections.index', compact('sections'));
    }

    public function create()
    {
        return view('sections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_name' => 'required|unique:sections',
        ]);

        Section::create([
            'section_name' => $request->section_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('sections.index')
            ->with('success', 'Section Added Successfully');
    }

    public function edit(Section $section)
    {
        return view('sections.edit', compact('section'));
    }

    public function update(Request $request, Section $section)
    {
        $request->validate([
            'section_name' =>
            'required|unique:sections,section_name,' . $section->id,
        ]);

        $section->update([
            'section_name' => $request->section_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('sections.index')
            ->with('success', 'Section Updated Successfully');
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()
            ->route('sections.index')
            ->with('success', 'Section Deleted Successfully');
    }
}