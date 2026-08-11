<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\JournalEntry;
use App\Models\Attachment;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'tipe' => 'required|in:update,pencapaian,issue,system',
            'tanggal' => 'required|date',
            'detail' => 'nullable|string',
            'task_id' => 'required|exists:tasks,id',
            'progress_pct' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:belum_mulai,in_progress,review,blocked,selesai',
            'lampiran.*' => 'nullable|file|max:10240',
            'tautan' => 'nullable|array',
            'tautan.*' => 'nullable|url',
        ]);

        $task = \App\Models\Task::find($validated['task_id']);
        $oldProgress = $task->progress_pct;
        $oldStatus = $task->status;

        $task->progress_pct = $validated['progress_pct'];
        $task->status = $validated['status'];
        if ($validated['status'] === 'selesai') {
            $task->progress_pct = 100;
            $task->completed_at = now();
        }
        $task->save();

        $progressService = app(\App\Services\ProgressService::class);
        if ($oldProgress != $task->progress_pct) {
            $progressService->createAutoLog('task', $task->id, 'progress_pct', $oldProgress, $task->progress_pct, auth()->id());
        }
        if ($oldStatus != $task->status) {
            $progressService->createAutoLog('task', $task->id, 'status', $oldStatus, $task->status, auth()->id());
        }
        $progressService->updateTaskProgress($task);

        $progressNote = "<p><strong>Update Sistem:</strong> Progress berubah dari <strong>{$oldProgress}%</strong> menjadi <strong>{$task->progress_pct}%</strong>. Status: <strong>" . str_replace('_', ' ', $task->status) . "</strong>.</p>";
        $fullDetail = $validated['detail'] ? $validated['detail'] . $progressNote : $progressNote;

        $journal = JournalEntry::create([
            'project_id' => $project->id,
            'phase_id' => $task->phase_id,
            'task_id' => $task->id,
            'tipe' => $validated['tipe'],
            'tanggal' => $validated['tanggal'],
            'judul' => $validated['judul'],
            'detail' => $fullDetail,
            'tautan' => array_filter($validated['tautan'] ?? []),
            'created_by' => auth()->id(),
        ]);

        // Handle file attachments
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store('attachments/journals', 'public');
                Attachment::create([
                    'attachable_type' => 'journal',
                    'attachable_id' => $journal->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'ukuran_bytes' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        if ($request->has('redirect_to') && $request->input('redirect_to') === 'dashboard') {
            return redirect()->route('dashboard')->with([
                'success' => 'Jurnal berhasil ditambahkan!',
                'expanded_group' => $project->group_id ?? 'ungrouped',
                'expanded_project' => $project->id,
                'expanded_phase' => $task->phase_id
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Jurnal berhasil ditambahkan!');
    }

    public function edit(JournalEntry $journal)
    {
        return view('journals.edit', compact('journal'));
    }

    public function update(Request $request, JournalEntry $journal)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'tipe' => 'required|in:update,pencapaian,issue,system',
            'tanggal' => 'required|date',
            'detail' => 'nullable|string',
            'progress_pct' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:belum_mulai,in_progress,review,blocked,selesai',
            'lampiran.*' => 'nullable|file|max:10240',
            'tautan' => 'nullable|array',
            'tautan.*' => 'nullable|url',
        ]);

        $fullDetail = $validated['detail'];

        $journal->update([
            'judul' => $validated['judul'],
            'tipe' => $validated['tipe'],
            'tanggal' => $validated['tanggal'],
            'detail' => $fullDetail,
            'tautan' => array_filter($validated['tautan'] ?? []),
        ]);

        // Handle additional file attachments on update
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store('attachments/journals', 'public');
                Attachment::create([
                    'attachable_type' => 'journal',
                    'attachable_id' => $journal->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'ukuran_bytes' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        if ($journal->task_id) {
            $task = \App\Models\Task::find($journal->task_id);
            $oldProgress = $task->progress_pct;
            $oldStatus = $task->status;

            $task->progress_pct = $validated['progress_pct'];
            $task->status = $validated['status'];
            if ($validated['status'] === 'selesai') {
                $task->progress_pct = 100;
                $task->completed_at = now();
            }
            $task->save();

            $progressService = app(\App\Services\ProgressService::class);
            if ($oldProgress != $task->progress_pct) {
                $progressService->createAutoLog('task', $task->id, 'progress_pct', $oldProgress, $task->progress_pct, auth()->id());
            }
            if ($oldStatus != $task->status) {
                $progressService->createAutoLog('task', $task->id, 'status', $oldStatus, $task->status, auth()->id());
            }
            $progressService->updateTaskProgress($task);
        }

        if ($request->has('redirect_to') && $request->input('redirect_to') === 'dashboard') {
            return redirect()->route('dashboard')->with([
                'success' => 'Jurnal berhasil diupdate!',
                'expanded_group' => $journal->project->group_id ?? 'ungrouped',
                'expanded_project' => $journal->project_id,
                'expanded_phase' => $journal->phase_id
            ]);
        }

        return redirect()->route('projects.show', $journal->project_id)->with('success', 'Jurnal berhasil diupdate!');
    }

    public function destroy(Request $request, JournalEntry $journal)
    {
        $projectId = $journal->project_id;
        $phaseId = $journal->phase_id;
        $groupId = $journal->project->group_id ?? 'ungrouped';
        
        $journal->delete();

        if ($request->has('redirect_to') && $request->input('redirect_to') === 'dashboard') {
            return redirect()->route('dashboard')->with([
                'success' => 'Jurnal berhasil dihapus!',
                'expanded_group' => $groupId,
                'expanded_project' => $projectId,
                'expanded_phase' => $phaseId
            ]);
        }

        return redirect()->route('projects.show', $projectId)->with('success', 'Jurnal berhasil dihapus!');
    }

    public function move(Request $request, JournalEntry $journal)
    {
        $validated = $request->validate([
            'target_task_id' => 'required|exists:tasks,id',
        ]);

        $targetTask = \App\Models\Task::findOrFail($validated['target_task_id']);
        
        // Update the journal's relationships
        $journal->update([
            'project_id' => $targetTask->phase->project_id,
            'phase_id' => $targetTask->phase_id,
            'task_id' => $targetTask->id,
        ]);

        if ($request->has('sync_progress') && $request->sync_progress == 1) {
            $sourceTask = \App\Models\Task::find($journal->task_id ?? $journal->getOriginal('task_id'));
            if ($sourceTask) {
                $oldProgress = $targetTask->progress_pct;
                $oldStatus = $targetTask->status;
                
                $targetTask->progress_pct = $sourceTask->progress_pct;
                $targetTask->status = $sourceTask->status;
                if ($targetTask->status === 'selesai') {
                    $targetTask->completed_at = now();
                }
                $targetTask->save();

                $progressService = app(\App\Services\ProgressService::class);
                if ($oldProgress != $targetTask->progress_pct) {
                    $progressService->createAutoLog('task', $targetTask->id, 'progress_pct', $oldProgress, $targetTask->progress_pct, auth()->id());
                }
                if ($oldStatus != $targetTask->status) {
                    $progressService->createAutoLog('task', $targetTask->id, 'status', $oldStatus, $targetTask->status, auth()->id());
                }
                $progressService->updateTaskProgress($targetTask);
            }
        }

        return redirect()->route('dashboard')->with([
            'success' => 'Jurnal beserta lampirannya berhasil dipindahkan. Harap sesuaikan kembali persentase Progress pada Task asal maupun Task tujuan secara manual bila diperlukan.',
            'expanded_group' => $targetTask->phase->project->group_id ?? 'ungrouped',
            'expanded_project' => $targetTask->phase->project_id,
            'expanded_phase' => $targetTask->phase_id
        ]);
    }

    public function bulkMove(Request $request)
    {
        $validated = $request->validate([
            'journal_ids' => 'required|array',
            'journal_ids.*' => 'exists:journal_entries,id',
            'target_task_id' => 'required|exists:tasks,id',
        ]);

        $targetTask = \App\Models\Task::findOrFail($validated['target_task_id']);
        
        \App\Models\JournalEntry::whereIn('id', $validated['journal_ids'])->update([
            'project_id' => $targetTask->phase->project_id,
            'phase_id' => $targetTask->phase_id,
            'task_id' => $targetTask->id,
        ]);

        if ($request->has('sync_progress') && $request->sync_progress == 1) {
            $firstJournal = \App\Models\JournalEntry::find($validated['journal_ids'][0]);
            $sourceTask = $firstJournal ? \App\Models\Task::find($firstJournal->getOriginal('task_id')) : null;
            if ($sourceTask) {
                $oldProgress = $targetTask->progress_pct;
                $oldStatus = $targetTask->status;
                
                $targetTask->progress_pct = $sourceTask->progress_pct;
                $targetTask->status = $sourceTask->status;
                if ($targetTask->status === 'selesai') {
                    $targetTask->completed_at = now();
                }
                $targetTask->save();

                $progressService = app(\App\Services\ProgressService::class);
                if ($oldProgress != $targetTask->progress_pct) {
                    $progressService->createAutoLog('task', $targetTask->id, 'progress_pct', $oldProgress, $targetTask->progress_pct, auth()->id());
                }
                if ($oldStatus != $targetTask->status) {
                    $progressService->createAutoLog('task', $targetTask->id, 'status', $oldStatus, $targetTask->status, auth()->id());
                }
                $progressService->updateTaskProgress($targetTask);
            }
        }

        return redirect()->route('dashboard')->with([
            'success' => count($validated['journal_ids']) . ' Jurnal berhasil dipindahkan. Harap sesuaikan persentase Progress secara manual bila diperlukan.',
            'expanded_group' => $targetTask->phase->project->group_id ?? 'ungrouped',
            'expanded_project' => $targetTask->phase->project_id,
            'expanded_phase' => $targetTask->phase_id
        ]);
    }

    public function copy(Request $request, JournalEntry $journal)
    {
        $validated = $request->validate([
            'target_task_id' => 'required|exists:tasks,id',
        ]);

        $targetTask = \App\Models\Task::findOrFail($validated['target_task_id']);
        
        $newJournal = $journal->replicate();
        $newJournal->project_id = $targetTask->phase->project_id;
        $newJournal->phase_id = $targetTask->phase_id;
        $newJournal->task_id = $targetTask->id;
        $newJournal->created_at = now();
        $newJournal->updated_at = now();
        $newJournal->save();

        foreach ($journal->attachments as $attachment) {
            $newAttachment = $attachment->replicate();
            $newAttachment->attachable_id = $newJournal->id;
            $newAttachment->save();
        }

        if ($request->has('sync_progress') && $request->sync_progress == 1) {
            $sourceTask = \App\Models\Task::find($journal->task_id);
            if ($sourceTask) {
                $oldProgress = $targetTask->progress_pct;
                $oldStatus = $targetTask->status;
                
                $targetTask->progress_pct = $sourceTask->progress_pct;
                $targetTask->status = $sourceTask->status;
                if ($targetTask->status === 'selesai') {
                    $targetTask->completed_at = now();
                }
                $targetTask->save();

                $progressService = app(\App\Services\ProgressService::class);
                if ($oldProgress != $targetTask->progress_pct) {
                    $progressService->createAutoLog('task', $targetTask->id, 'progress_pct', $oldProgress, $targetTask->progress_pct, auth()->id());
                }
                if ($oldStatus != $targetTask->status) {
                    $progressService->createAutoLog('task', $targetTask->id, 'status', $oldStatus, $targetTask->status, auth()->id());
                }
                $progressService->updateTaskProgress($targetTask);
            }
        }

        return redirect()->route('dashboard')->with([
            'success' => 'Jurnal beserta lampirannya berhasil disalin. Harap sesuaikan persentase Progress pada Task tujuan secara manual bila diperlukan.',
            'expanded_group' => $targetTask->phase->project->group_id ?? 'ungrouped',
            'expanded_project' => $targetTask->phase->project_id,
            'expanded_phase' => $targetTask->phase_id
        ]);
    }

    public function bulkCopy(Request $request)
    {
        $validated = $request->validate([
            'journal_ids' => 'required|array',
            'journal_ids.*' => 'exists:journal_entries,id',
            'target_task_id' => 'required|exists:tasks,id',
        ]);

        $targetTask = \App\Models\Task::findOrFail($validated['target_task_id']);
        $journals = \App\Models\JournalEntry::with('attachments')->whereIn('id', $validated['journal_ids'])->get();
        
        foreach ($journals as $journal) {
            $newJournal = $journal->replicate();
            $newJournal->project_id = $targetTask->phase->project_id;
            $newJournal->phase_id = $targetTask->phase_id;
            $newJournal->task_id = $targetTask->id;
            $newJournal->created_at = now();
            $newJournal->updated_at = now();
            $newJournal->save();

            foreach ($journal->attachments as $attachment) {
                $newAttachment = $attachment->replicate();
                $newAttachment->attachable_id = $newJournal->id;
                $newAttachment->save();
            }
        }

        if ($request->has('sync_progress') && $request->sync_progress == 1) {
            $firstJournal = \App\Models\JournalEntry::find($validated['journal_ids'][0]);
            $sourceTask = $firstJournal ? \App\Models\Task::find($firstJournal->task_id) : null;
            if ($sourceTask) {
                $oldProgress = $targetTask->progress_pct;
                $oldStatus = $targetTask->status;
                
                $targetTask->progress_pct = $sourceTask->progress_pct;
                $targetTask->status = $sourceTask->status;
                if ($targetTask->status === 'selesai') {
                    $targetTask->completed_at = now();
                }
                $targetTask->save();

                $progressService = app(\App\Services\ProgressService::class);
                if ($oldProgress != $targetTask->progress_pct) {
                    $progressService->createAutoLog('task', $targetTask->id, 'progress_pct', $oldProgress, $targetTask->progress_pct, auth()->id());
                }
                if ($oldStatus != $targetTask->status) {
                    $progressService->createAutoLog('task', $targetTask->id, 'status', $oldStatus, $targetTask->status, auth()->id());
                }
                $progressService->updateTaskProgress($targetTask);
            }
        }

        return redirect()->route('dashboard')->with([
            'success' => count($journals) . ' Jurnal berhasil disalin. Harap sesuaikan persentase Progress secara manual bila diperlukan.',
            'expanded_group' => $targetTask->phase->project->group_id ?? 'ungrouped',
            'expanded_project' => $targetTask->phase->project_id,
            'expanded_phase' => $targetTask->phase_id
        ]);
    }
}
