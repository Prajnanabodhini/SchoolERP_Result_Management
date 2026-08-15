<?php

namespace App\Http\Controllers;

use App\Models\ExamPattern;
use Illuminate\Http\Request;

class ExamPatternController extends Controller
{
    public function index()
    {
        $patterns = ExamPattern::orderBy('id', 'desc')->get();

        return view('exam-patterns.index', compact('patterns'));
    }


    public function create()
    {
        return view('exam-patterns.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'pattern_name' => 'required|max:255',
            'pattern_type' => 'nullable|max:255',
            'description'  => 'nullable',
        ]);

        ExamPattern::create([
            'pattern_name' => $request->pattern_name,
            'pattern_type' => $request->pattern_type,
            'description'  => $request->description,
            'is_active'    => 1,
        ]);

        return redirect()
            ->route('exam-patterns.index')
            ->with('success','Exam Pattern created successfully.');
    }


    public function show(ExamPattern $examPattern)
    {
        return view('exam-patterns.show', compact('examPattern'));
    }


    public function edit(ExamPattern $examPattern)
    {
        return view('exam-patterns.edit', compact('examPattern'));
    }


    public function update(Request $request, ExamPattern $examPattern)
    {
        $request->validate([
            'pattern_name' => 'required|max:255',
            'pattern_type' => 'nullable|max:255',
            'description'  => 'nullable',
        ]);

        $examPattern->update([
            'pattern_name' => $request->pattern_name,
            'pattern_type' => $request->pattern_type,
            'description'  => $request->description,
        ]);

        return redirect()
            ->route('exam-patterns.index')
            ->with('success','Exam Pattern updated successfully.');
    }


    public function destroy(ExamPattern $examPattern)
    {
        $examPattern->delete();

        return redirect()
            ->route('exam-patterns.index')
            ->with('success','Exam Pattern deleted successfully.');
    }
}