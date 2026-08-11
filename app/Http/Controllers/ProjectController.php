<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectGroup;
use App\Models\User;
use App\Models\Pic;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        $project->load(['pm', 'phases.tasks.pics', 'phases.pic']);
        return view('projects.show', compact('project'));
    }

    public function create()
    {
        $groups = ProjectGroup::all();
        $pms = User::whereIn('role', ['pm', 'admin'])->get();
        $pics = Pic::all();
        return view('projects.create', compact('groups', 'pms', 'pics'));
    }

    public function gantt(Project $project)
    {
        $project->load(['phases.tasks' => function($q) {
            $q->orderBy('tanggal_mulai');
        }]);
        
        $minDate = null;
        $maxDate = null;
        
        foreach ($project->phases as $phase) {
            foreach ($phase->tasks as $task) {
                if ($task->tanggal_mulai) {
                    $start = \Carbon\Carbon::parse($task->tanggal_mulai);
                    if (!$minDate || $start < $minDate) $minDate = $start->copy();
                }
                if ($task->deadline) {
                    $end = \Carbon\Carbon::parse($task->deadline);
                    if (!$maxDate || $end > $maxDate) $maxDate = $end->copy();
                }
            }
        }
        
        if (!$minDate) $minDate = now();
        if (!$maxDate) $maxDate = now()->addDays(30);
        
        $minDate = $minDate->subDays(5);
        $maxDate = $maxDate->addDays(5);
        
        $totalDays = $minDate->diffInDays($maxDate) + 1;
        
        return view('projects.gantt', compact('project', 'minDate', 'maxDate', 'totalDays'));
    }

    public function globalGantt()
    {
        $projects = Project::with(['phases.tasks'])->get();
        
        $minDate = null;
        $maxDate = null;
        $projectDates = [];
        
        foreach ($projects as $project) {
            $pStart = $project->created_at;
            $pEnd = $project->target_selesai ? \Carbon\Carbon::parse($project->target_selesai) : $project->created_at->copy()->addDays(30);
            
            foreach ($project->phases as $phase) {
                foreach ($phase->tasks as $task) {
                    if ($task->tanggal_mulai) {
                        $start = \Carbon\Carbon::parse($task->tanggal_mulai);
                        if ($start < $pStart) $pStart = $start->copy();
                    }
                    if ($task->deadline) {
                        $end = \Carbon\Carbon::parse($task->deadline);
                        if ($end > $pEnd) $pEnd = $end->copy();
                    }
                }
            }
            
            if (!$minDate || $pStart < $minDate) $minDate = $pStart->copy();
            if (!$maxDate || $pEnd > $maxDate) $maxDate = $pEnd->copy();
            
            $projectDates[$project->id] = [
                'start' => $pStart,
                'end' => $pEnd
            ];
        }
        
        if (!$minDate) $minDate = now();
        if (!$maxDate) $maxDate = now()->addDays(30);
        
        $minDate = $minDate->subDays(5);
        $maxDate = $maxDate->addDays(5);
        
        $totalDays = $minDate->diffInDays($maxDate) + 1;
        
        return view('projects.global_gantt', compact('projects', 'minDate', 'maxDate', 'totalDays', 'projectDates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_proyek' => 'required|string|max:150',
            'group_id' => 'required|exists:project_groups,id',
            'pm_user_id' => 'required|exists:users,id',
            'pics' => 'nullable|array',
            'pics.*' => 'exists:pics,id',
            'bobot_pct' => 'required|numeric|min:0|max:100',
            'prioritas' => 'required|in:low,medium,high,urgent',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date',
            'deskripsi' => 'nullable|string',
        ]);

        $project = Project::create([
            'nama_proyek' => $validated['nama_proyek'],
            'group_id' => $validated['group_id'],
            'pm_user_id' => $validated['pm_user_id'],
            'bobot_pct' => $validated['bobot_pct'],
            'prioritas' => $validated['prioritas'],
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'target_selesai' => $validated['target_selesai'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'status' => 'berjalan',
            'health_score' => 100,
            'health_status' => 'healthy',
            'is_bobot_seimbang' => true,
        ]);
        
        if (!empty($validated['pics'])) {
            $project->pics()->sync($validated['pics']);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil dibuat!');
    }

    public function edit(Project $project)
    {
        $groups = ProjectGroup::all();
        $pms = User::whereIn('role', ['pm', 'admin'])->get();
        $pics = Pic::all();
        return view('projects.edit', compact('project', 'groups', 'pms', 'pics'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'nama_proyek' => 'required|string|max:150',
            'group_id' => 'required|exists:project_groups,id',
            'pm_user_id' => 'required|exists:users,id',
            'pics' => 'nullable|array',
            'pics.*' => 'exists:pics,id',
            'bobot_pct' => 'required|numeric|min:0|max:100',
            'prioritas' => 'required|in:low,medium,high,urgent',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date',
            'deskripsi' => 'nullable|string',
        ]);

        $project->update([
            'nama_proyek' => $validated['nama_proyek'],
            'group_id' => $validated['group_id'],
            'pm_user_id' => $validated['pm_user_id'],
            'bobot_pct' => $validated['bobot_pct'],
            'prioritas' => $validated['prioritas'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'target_selesai' => $validated['target_selesai'],
            'deskripsi' => $validated['deskripsi'],
        ]);
        
        if (isset($validated['pics'])) {
            $project->pics()->sync($validated['pics']);
        } else {
            $project->pics()->sync([]);
        }
        
        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui!');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('dashboard')->with('success', 'Proyek berhasil dihapus!');
    }
}
