<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|max:255|unique:users,name',
        'email' => 'required|email|unique:users,email',
        'role' => 'required',
        'password' => 'required|min:6',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'is_active' => $request->has('is_active'),
        'password' => $request->password,
    ]);

    return redirect()
        ->route('users.index')
        ->with('success', 'User created successfully.');
}

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate(
            [
                'name' => 'required|max:255|unique:users,name,' . $user->id,
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required',
            ],
            [
                'name.unique' => 'Username already exists. Please choose another username.',
            ]
        );

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($request->password)) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}