<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Phase;
use App\Models\Pic;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function create(Project $project)
    {
        $existingPhases = Phase::where('project_id', $project->id)->get();
        $sisaBobot = 100 - $existingPhases->sum('bobot_pct');
        $pics = Pic::all();
        return view('phases.create', compact('project', 'sisaBobot', 'pics'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'nama_fase' => 'required|string|max:150',
            'bobot_pct' => 'required|numeric|min:0|max:100',
            'pic_id' => 'nullable|exists:pics,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_target' => 'nullable|date',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['project_id'] = $project->id;
        $validated['urutan'] = $project->phases()->count() + 1;
        
        $phase = Phase::create($validated);
        
        \App\Models\JournalEntry::create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'tipe' => 'system',
            'judul' => 'Fase baru ditambahkan: ' . $phase->nama_fase,
            'detail' => '<p>Fase <strong>' . $phase->nama_fase . '</strong> (bobot ' . $phase->bobot_pct . '%) ditambahkan ke proyek.</p>',
            'created_by' => auth()->id(),
        ]);

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->updateProjectProgress($project);
        $progressService->validateBobotSeimbang($project);

        return redirect()->route('projects.show', $project)->with('success', 'Fase berhasil ditambahkan!');
    }

    public function edit(Phase $phase)
    {
        $existingPhases = Phase::where('project_id', $phase->project_id)->where('id', '!=', $phase->id)->get();
        $sisaBobot = 100 - $existingPhases->sum('bobot_pct');
        $pics = Pic::all();
        return view('phases.edit', compact('phase', 'sisaBobot', 'pics'));
    }

    public function update(Request $request, Phase $phase)
    {
        $validated = $request->validate([
            'nama_fase' => 'required|string|max:150',
            'bobot_pct' => 'required|numeric|min:0|max:100',
            'pic_id' => 'nullable|exists:pics,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_target' => 'nullable|date',
            'deskripsi' => 'nullable|string',
        ]);

        $phase->update($validated);

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->validateBobotSeimbang($phase->project);

        return redirect()->route('projects.show', $phase->project_id)->with('success', 'Fase berhasil diperbarui!');
    }

    public function destroy(Phase $phase)
    {
        $projectId = $phase->project_id;
        $phase->delete();

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->validateBobotSeimbang(Project::find($projectId));
        
        return redirect()->route('projects.show', $projectId)->with('success', 'Fase berhasil dihapus!');
    }

    public function copy(Request $request, Phase $phase)
    {
        $validated = $request->validate([
            'target_project_id' => 'required|exists:projects,id',
            'include_tasks' => 'nullable|boolean'
        ]);

        $newPhase = $phase->replicate();
        $newPhase->project_id = $validated['target_project_id'];
        $newPhase->progress_pct = 0;
        $newPhase->urutan = Phase::where('project_id', $validated['target_project_id'])->max('urutan') + 1;
        $newPhase->save();

        if ($request->boolean('include_tasks')) {
            foreach ($phase->tasks as $task) {
                $newTask = $task->replicate();
                $newTask->phase_id = $newPhase->id;
                $newTask->progress_pct = 0;
                $newTask->status = 'belum_mulai';
                $newTask->save();
                
                foreach ($task->pics as $pic) {
                    $newTask->pics()->attach($pic->id, ['peran' => $pic->pivot->peran ?? 'utama']);
                }
            }
        }

        JournalEntry::create([
            'project_id' => $validated['target_project_id'],
            'phase_id' => $newPhase->id,
            'tipe' => 'system',
            'judul' => 'Fase disalin: ' . $newPhase->nama_fase,
            'detail' => '<p>Fase <strong>' . $newPhase->nama_fase . '</strong> berhasil disalin dari proyek lain.</p>',
            'created_by' => auth()->id(),
        ]);

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->validateBobotSeimbang(Project::find($validated['target_project_id']));

        return redirect()->back()->with('success', 'Fase beserta detailnya berhasil disalin!');
    }
}
