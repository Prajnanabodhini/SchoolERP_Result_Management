<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::orderBy('display_order')->get();

        return view('divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_name' => 'required|unique:divisions',
        ]);

        Division::create([
            'division_name' => $request->division_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division Added Successfully');
    }

    public function edit(Division $division)
    {
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'division_name' => 'required|unique:divisions,division_name,' . $division->id,
        ]);

        $division->update([
            'division_name' => $request->division_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division Updated Successfully');
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division Deleted Successfully');
    }
}