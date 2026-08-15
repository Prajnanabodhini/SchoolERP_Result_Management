<?php

namespace App\Http\Controllers;

use App\Models\Standard;
use Illuminate\Http\Request;

class StandardController extends Controller
{
    public function index()
    {
        $standards = Standard::orderBy('display_order')->get();

        return view('standards.index', compact('standards'));
    }

    public function create()
    {
        return view('standards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'standard_name' => 'required|unique:standards',
        ]);

        Standard::create([
            'standard_name' => $request->standard_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('standards.index')
            ->with('success', 'Standard Added Successfully');
    }

    public function edit(Standard $standard)
    {
        return view('standards.edit', compact('standard'));
    }

    public function update(Request $request, Standard $standard)
    {
        $request->validate([
            'standard_name' => 'required|unique:standards,standard_name,' . $standard->id,
        ]);

        $standard->update([
            'standard_name' => $request->standard_name,
            'display_order' => $request->display_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('standards.index')
            ->with('success', 'Standard Updated Successfully');
    }

    public function destroy(Standard $standard)
    {
        $standard->delete();

        return redirect()
            ->route('standards.index')
            ->with('success', 'Standard Deleted Successfully');
    }
}