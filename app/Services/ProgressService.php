<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Phase;
use App\Models\Task;
use App\Models\ChecklistItem;

class ProgressService
{
    /**
     * Update progress of a task based on its subtasks or checklists
     */
    public function updateTaskProgress(Task $task)
    {
        if ($task->progress_source === 'checklist') {
            $items = ChecklistItem::where('task_id', $task->id)->get();
            if ($items->count() > 0) {
                $completed = $items->where('is_done', true)->count();
                $task->progress_pct = ($completed / $items->count()) * 100;
                if ($task->progress_pct == 100) $task->status = 'selesai';
                $task->save();
            }
        } elseif ($task->progress_source === 'subtask') {
            $subtasks = Task::where('parent_task_id', $task->id)->get();
            if ($subtasks->count() > 0) {
                $totalBobot = $subtasks->sum('bobot_pct');
                if ($totalBobot > 0) {
                    $progress = 0;
                    foreach ($subtasks as $st) {
                        $progress += ($st->progress_pct * $st->bobot_pct);
                    }
                    $task->progress_pct = $progress / $totalBobot;
                    if ($task->progress_pct == 100) $task->status = 'selesai';
                    $task->save();
                }
            }
        }

        // Trigger parent calculation recursively
        if ($task->parent_task_id) {
            $this->updateTaskProgress(Task::find($task->parent_task_id));
        } elseif ($task->phase_id) {
            $this->updatePhaseProgress(Phase::find($task->phase_id));
        }
    }

    /**
     * Update progress of a phase based on its tasks
     */
    public function updatePhaseProgress(Phase $phase)
    {
        $tasks = Task::where('phase_id', $phase->id)->whereNull('parent_task_id')->get();
        if ($tasks->count() > 0) {
            $totalBobot = $tasks->sum('bobot_pct');
            if ($totalBobot > 0) {
                $progress = 0;
                foreach ($tasks as $t) {
                    $progress += ($t->progress_pct * $t->bobot_pct);
                }
                $phase->progress_pct = $progress / $totalBobot;
                
                if ($phase->progress_pct == 100) {
                    $phase->status = 'selesai';
                    $phase->completed_at = now();
                } else if ($phase->progress_pct > 0 && $phase->status == 'belum_mulai') {
                    $phase->status = 'in_progress';
                }
                $phase->save();
            }
        }

        // Trigger Project calculation
        if ($phase->project_id) {
            $this->updateProjectProgress(Project::find($phase->project_id));
        }
    }

    /**
     * Update progress of a project based on its phases
     */
    public function updateProjectProgress(Project $project)
    {
        $phases = Phase::where('project_id', $project->id)->get();
        if ($phases->count() > 0) {
            $totalBobot = $phases->sum('bobot_pct');
            if ($totalBobot > 0) {
                $progress = 0;
                foreach ($phases as $p) {
                    $progress += ($p->progress_pct * $p->bobot_pct);
                }
                $project->progress_pct = $progress / $totalBobot;
                
                if ($project->progress_pct == 100) {
                    $project->status = 'selesai';
                    $project->completed_at = now();
                } else if ($project->progress_pct > 0 && $project->status == 'draft') {
                    $project->status = 'berjalan';
                }
                $project->save();
            }
        }
        
        $this->updateHealthScore($project);
    }

    public function updateHealthScore(Project $project)
    {
        $score = 0;

        // 1. Schedule Variance (40%)
        $scheduleScore = 40;
        if ($project->tanggal_mulai && $project->target_selesai) {
            $totalDays = \Carbon\Carbon::parse($project->tanggal_mulai)->diffInDays($project->target_selesai);
            if ($totalDays > 0) {
                $elapsedDays = \Carbon\Carbon::parse($project->tanggal_mulai)->diffInDays(now(), false);
                $elapsedDays = max(0, min($elapsedDays, $totalDays));
                $expectedProgress = ($elapsedDays / $totalDays) * 100;
                
                if ($expectedProgress > 0) {
                    $varianceRatio = $project->progress_pct / $expectedProgress;
                    $scheduleScore = min(40, max(0, $varianceRatio * 40));
                }
            }
        }
        $score += $scheduleScore;

        // 2. Task Overdue (30%)
        $taskScore = 30;
        $phaseIds = $project->phases()->pluck('id');
        $allTasks = Task::whereIn('phase_id', $phaseIds)->get();
        
        if ($allTasks->count() > 0) {
            $overdueCount = 0;
            foreach ($allTasks as $task) {
                if ($task->deadline && $task->deadline < now() && $task->status !== 'selesai') {
                    $overdueCount++;
                }
            }
            $overdueRatio = $overdueCount / $allTasks->count();
            $taskScore = 30 - ($overdueRatio * 30);
        }
        $score += $taskScore;

        // 3. Recent Activity (15%)
        $activityScore = 0;
        $lastUpdate = null;
        
        $lastJournal = \App\Models\JournalEntry::where('project_id', $project->id)->latest()->first();
        if ($lastJournal) {
            $lastUpdate = $lastJournal->created_at;
        }
        $lastTaskUpdate = Task::whereIn('phase_id', $phaseIds)->max('updated_at');
        if ($lastTaskUpdate && (!$lastUpdate || $lastTaskUpdate > $lastUpdate)) {
            $lastUpdate = $lastTaskUpdate;
        }

        if ($lastUpdate) {
            $daysSinceActivity = \Carbon\Carbon::parse($lastUpdate)->diffInDays(now());
            if ($daysSinceActivity <= 3) $activityScore = 15;
            elseif ($daysSinceActivity <= 7) $activityScore = 10;
            elseif ($daysSinceActivity <= 14) $activityScore = 5;
            else $activityScore = 0;
        } else {
            $activityScore = 0; // No activity yet
        }
        $score += $activityScore;

        // 4. Blocked Tasks (15%)
        $blockedScore = 15;
        if ($allTasks->count() > 0) {
            $blockedCount = $allTasks->where('status', 'blocked')->count();
            $blockedRatio = $blockedCount / $allTasks->count();
            $blockedScore = 15 - ($blockedRatio * 15);
        }
        $score += $blockedScore;

        $score = (int) round($score);
        $project->health_score = $score;
        if ($score >= 80) $project->health_status = 'healthy';
        elseif ($score >= 60) $project->health_status = 'attention';
        elseif ($score >= 35) $project->health_status = 'at_risk';
        else $project->health_status = 'critical';

        $project->save();
    }

    public function validateBobotSeimbang(Project $project)
    {
        $phases = Phase::where('project_id', $project->id)->get();
        $isSeimbang = true;
        
        if ($phases->sum('bobot_pct') != 100) {
            $isSeimbang = false;
        } else {
            foreach ($phases as $phase) {
                $tasks = Task::where('phase_id', $phase->id)->whereNull('parent_task_id')->get();
                if ($tasks->count() > 0 && $tasks->sum('bobot_pct') != 100) {
                    $isSeimbang = false;
                    break;
                }
            }
        }
        
        $project->is_bobot_seimbang = $isSeimbang;
        $project->save();
    }

    public function createAutoLog($entityType, $entityId, $fieldChanged, $oldValue, $newValue, $userId)
    {
        \App\Models\AuditLog::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId,
        ]);
    }
}
