<?php

namespace App\Http\Controllers;

use App\Models\SubjectType;
use Illuminate\Http\Request;

class SubjectTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $subjectTypes = SubjectType::orderBy('type_name')->get();

    return view('subject-types.index', compact('subjectTypes'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    return view('subject-types.create');
}
    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $request->validate([
        'type_name' => 'required|unique:subject_types,type_name',
    ]);

    SubjectType::create([
        'type_name'  => $request->type_name,
        'description'=> $request->description,
        'is_active'  => $request->has('is_active'),
    ]);

    return redirect('/subject-types')
        ->with('success', 'Subject Type added successfully.');
}
    /**
     * Display the specified resource.
     */
    public function show(SubjectType $subjectType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubjectType $subjectType)
{
    return view('subject-types.edit', compact('subjectType'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubjectType $subjectType)
{
    $request->validate([
        'type_name' => 'required'
    ]);

    $subjectType->update([
        'type_name' => $request->type_name,
        'description' => $request->description,
        'is_active' => $request->has('is_active'),
    ]);

    return redirect('/subject-types')
        ->with('success', 'Subject Type updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubjectType $subjectType)
{
    $subjectType->delete();

    return redirect('/subject-types')
        ->with('success', 'Subject Type deleted successfully.');
}
}
