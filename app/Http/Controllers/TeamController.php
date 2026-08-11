<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TeamController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->get();
        return view('teams.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,pm,pic,stakeholder',
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        if (in_array($validated['role'], ['pm', 'pic'])) {
            Pic::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
            ]);
        }

        return redirect()->route('teams.index')->with('success', 'Anggota tim berhasil ditambahkan!');
    }

    public function edit(User $team)
    {
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, User $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$team->id,
            'role' => 'required|in:admin,pm,pic,stakeholder',
            'password' => ['nullable', Password::defaults()],
        ]);

        $team->name = $validated['name'];
        $team->email = $validated['email'];
        $team->role = $validated['role'];
        
        if (!empty($validated['password'])) {
            $team->password = Hash::make($validated['password']);
        }
        
        $team->save();

        if (in_array($validated['role'], ['pm', 'pic'])) {
            Pic::updateOrCreate(
                ['user_id' => $team->id],
                ['nama' => $team->name, 'email' => $team->email]
            );
        }

        return redirect()->route('teams.index')->with('success', 'Anggota tim berhasil diperbarui!');
    }

    public function destroy(User $team)
    {
        // Transfer ownership of related records to an Admin to avoid foreign key constraints
        $admin = User::where('role', 'admin')->where('id', '!=', $team->id)->first();
        $adminId = $admin ? $admin->id : 1;

        \App\Models\ProjectGroup::where('created_by', $team->id)->update(['created_by' => $adminId]);
        
        if (class_exists(\App\Models\JournalEntry::class)) {
            \App\Models\JournalEntry::where('created_by', $team->id)->update(['created_by' => $adminId]);
        }
        
        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::where('changed_by', $team->id)->update(['changed_by' => $adminId]);
        }
        
        if (class_exists(\App\Models\JournalMention::class)) {
            \App\Models\JournalMention::where('user_id', $team->id)->delete();
        }

        $team->delete();
        return redirect()->route('teams.index')->with('success', 'Anggota tim berhasil dihapus!');
    }
}
