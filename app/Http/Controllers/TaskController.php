<?php
namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\Task;
use App\Models\Pic;
use App\Models\Attachment;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Phase $phase)
    {
        $phase->load('project');
        $pics = Pic::all();
        $existingTasks = Task::where('phase_id', $phase->id)->whereNull('parent_task_id')->get();
        $sisaBobot = 100 - $existingTasks->sum('bobot_pct');
        return view('tasks.create', compact('phase', 'pics', 'sisaBobot'));
    }

    public function store(Request $request, Phase $phase)
    {
        $validated = $request->validate([
            'nama_task' => 'required|string|max:200',
            'bobot_pct' => 'required|numeric|min:0|max:100',
            'prioritas' => 'required|in:low,medium,high',
            'tanggal_mulai' => 'nullable|date',
            'deadline' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'pic_utama' => 'nullable|exists:pics,id',
            'kontributor' => 'nullable|array',
            'kontributor.*' => 'exists:pics,id',
            'lampiran.*' => 'nullable|file|max:10240',
        ]);

        $task = Task::create([
            'phase_id' => $phase->id,
            'nama_task' => $validated['nama_task'],
            'bobot_pct' => $validated['bobot_pct'],
            'prioritas' => $validated['prioritas'],
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        // Attach PIC utama
        if (!empty($validated['pic_utama'])) {
            $task->pics()->attach($validated['pic_utama'], ['peran' => 'utama']);
        }
        // Attach kontributor
        if (!empty($validated['kontributor'])) {
            foreach ($validated['kontributor'] as $kontribId) {
                if ($kontribId != ($validated['pic_utama'] ?? null)) {
                    $task->pics()->attach($kontribId, ['peran' => 'kontributor']);
                }
            }
        }
        // Handle file uploads
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store('attachments/tasks', 'public');
                Attachment::create([
                    'attachable_type' => 'task',
                    'attachable_id' => $task->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'ukuran_bytes' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        // Auto-log
        JournalEntry::create([
            'project_id' => $phase->project_id,
            'phase_id' => $phase->id,
            'task_id' => $task->id,
            'tipe' => 'system',
            'judul' => 'Task baru ditambahkan: ' . $task->nama_task,
            'detail' => '<p>Task <strong>' . $task->nama_task . '</strong> (bobot ' . $task->bobot_pct . '%) ditambahkan ke fase <strong>' . $phase->nama_fase . '</strong>.</p>',
            'created_by' => auth()->id(),
        ]);

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->updatePhaseProgress($phase);
        $progressService->validateBobotSeimbang($phase->project);

        return redirect()->route('projects.show', $phase->project_id)->with('success', 'Task berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        $task->load('phase.project', 'pics');
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'progress_pct' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:belum_mulai,in_progress,review,blocked,selesai',
        ]);

        $oldProgress = $task->progress_pct;
        $oldStatus = $task->status;
        
        if ($validated['status'] === 'selesai') {
            $validated['progress_pct'] = 100;
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        // Auto-log progress change
        JournalEntry::create([
            'project_id' => $task->phase->project_id,
            'phase_id' => $task->phase_id,
            'task_id' => $task->id,
            'tipe' => 'system',
            'judul' => 'Progress diperbarui: ' . $task->nama_task,
            'detail' => '<p>Progress <strong>' . $task->nama_task . '</strong> berubah dari ' . $oldProgress . '% menjadi ' . $validated['progress_pct'] . '%. Status: ' . str_replace('_', ' ', $validated['status']) . '.</p>',
            'created_by' => auth()->id(),
        ]);

        // Audit log
        $progressService = app(\App\Services\ProgressService::class);
        if ($oldProgress != $validated['progress_pct']) {
            $progressService->createAutoLog('task', $task->id, 'progress_pct', $oldProgress, $validated['progress_pct'], auth()->id());
        }
        if ($oldStatus != $validated['status']) {
            $progressService->createAutoLog('task', $task->id, 'status', $oldStatus, $validated['status'], auth()->id());
        }

        $progressService->updateTaskProgress($task);

        return redirect()->route('projects.show', $task->phase->project_id)->with('success', 'Progress berhasil diperbarui!');
    }

    public function destroy(Task $task)
    {
        $projectId = $task->phase->project_id;
        $task->delete();

        $progressService = app(\App\Services\ProgressService::class);
        $progressService->updatePhaseProgress($task->phase);
        $progressService->validateBobotSeimbang($task->phase->project);

        return redirect()->route('projects.show', $projectId)->with('success', 'Task berhasil dihapus!');
    }

    public function move(Request $request, Task $task)
    {
        $validated = $request->validate([
            'target_phase_id' => 'required|exists:phases,id',
        ]);

        $oldPhaseId = $task->phase_id;
        $newPhaseId = $validated['target_phase_id'];

        if ($oldPhaseId != $newPhaseId) {
            $task->phase_id = $newPhaseId;
            $task->save();

            // Recalculate progress for both old and new phases
            $progressService = app(\App\Services\ProgressService::class);
            
            $oldPhase = Phase::find($oldPhaseId);
            if ($oldPhase) {
                $progressService->updatePhaseProgress($oldPhase);
                $progressService->validateBobotSeimbang($oldPhase->project);
            }
            
            $newPhase = Phase::find($newPhaseId);
            if ($newPhase) {
                $progressService->updatePhaseProgress($newPhase);
                $progressService->validateBobotSeimbang($newPhase->project);
            }

            JournalEntry::create([
                'project_id' => $newPhase->project_id,
                'phase_id' => $newPhase->id,
                'task_id' => $task->id,
                'tipe' => 'system',
                'judul' => 'Task dipindahkan: ' . $task->nama_task,
                'detail' => '<p>Task <strong>' . $task->nama_task . '</strong> dipindahkan ke fase <strong>' . $newPhase->nama_fase . '</strong>.</p>',
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
