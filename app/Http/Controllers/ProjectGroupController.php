<?php

namespace App\Http\Controllers;

use App\Models\ProjectGroup;
use Illuminate\Http\Request;

class ProjectGroupController extends Controller
{
    public function index()
    {
        $groups = ProjectGroup::withCount('projects')->get();
        return view('groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_grup' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        ProjectGroup::create($validated);

        return redirect()->route('groups.index')->with('success', 'Grup Proyek berhasil ditambahkan!');
    }

    public function edit(ProjectGroup $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, ProjectGroup $group)
    {
        $validated = $request->validate([
            'nama_grup' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
        ]);

        $group->update($validated);

        return redirect()->route('groups.index')->with('success', 'Grup Proyek berhasil diperbarui!');
    }

    public function destroy(ProjectGroup $group)
    {
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grup Proyek berhasil dihapus!');
    }
}
