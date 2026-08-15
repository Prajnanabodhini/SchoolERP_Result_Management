<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

public function store(Request $request)
{
$request->validate([
'name' => 'required|max:100',
]);


Role::create([
    'name' => $request->name,
    'description' => $request->description,
    'is_active' => $request->has('is_active'),
]);

return redirect()
    ->route('roles.index')
    ->with('success', 'Role created successfully.');


}


    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|max:100',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
