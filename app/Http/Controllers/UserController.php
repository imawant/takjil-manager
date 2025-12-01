<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Admin can manage Petugas and Guests
        $users = \App\Models\User::where('role', '!=', 'admin')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:petugas,guest',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = \App\Models\User::create($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'created',
            "Menambah petugas {$user->name} ({$user->role})",
            'User',
            $user->id
        );

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:petugas,guest',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'updated',
            "Mengubah data petugas {$user->name} ({$user->role})",
            'User',
            $user->id
        );

        return redirect()->back()->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(\App\Models\User $user)
    {
        $userName = $user->name;
        $userRole = $user->role;
        $userId = $user->id;
        
        $user->delete();
        
        // Log activity
        \App\Models\ActivityLog::log(
            'deleted',
            "Menghapus petugas {$userName} ({$userRole})",
            'User',
            $userId
        );
        
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }
}
