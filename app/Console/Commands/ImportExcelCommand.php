<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectGroup;
use App\Models\Project;
use App\Models\Phase;
use App\Models\Task;
use App\Models\User;
use App\Models\Pic;

class ImportExcelCommand extends Command
{
    protected $signature = 'app:import-excel';
    protected $description = 'Import Tracker Konsolidasi Excel data to Database';

    public function handle()
    {
        $jsonPath = storage_path('app/tracker.json');
        if (!file_exists($jsonPath)) {
            $this->error("JSON file not found at $jsonPath");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        
        // Find or create PM
        $pm = User::where('role', 'pm')->first() ?? User::first();
        if (!$pm) {
            $this->error("No users found to set as PM.");
            return;
        }

        // Find or create Group
        $group = ProjectGroup::firstOrCreate(
            ['nama_grup' => 'Grup Konsolidasi'],
            ['deskripsi' => 'Grup untuk proyek migrasi Excel', 'created_by' => $pm->id]
        );

        // Create Project
        $project = Project::create([
            'nama_proyek' => 'Tracker Konsolidasi (Migrasi)',
            'group_id' => $group->id,
            'pm_user_id' => $pm->id,
            'bobot_pct' => 100,
            'status' => 'berjalan',
            'health_score' => 100,
            'health_status' => 'healthy',
            'tanggal_mulai' => '2026-03-01', // based on Mar-W1
            'target_selesai' => '2026-08-31', // based on Agu-W4
        ]);

        $this->info("Project created: " . $project->nama_proyek);

        $currentPhase = null;
        $currentPhaseExcelBobot = 1;
        $urutanPhase = 1;

        // Skip header row
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            if (empty($row) || !isset($row[0])) continue;

            $name = $row[0];
            if ($name === null) continue;

            $excelBobot = isset($row[1]) ? (float)$row[1] : 0;
            $progressVal = isset($row[26]) ? (float)$row[26] : 0;
            
            if (str_starts_with(strtoupper($name), 'FASE')) {
                // It's a Phase
                $currentPhaseExcelBobot = $excelBobot > 0 ? $excelBobot : 1;
                $phaseBobotPct = $excelBobot * 100;
                
                $currentPhase = Phase::create([
                    'project_id' => $project->id,
                    'nama_fase' => $name,
                    'urutan' => $urutanPhase++,
                    'bobot_pct' => $phaseBobotPct,
                    'status' => $progressVal == 1 ? 'selesai' : ($progressVal > 0 ? 'in_progress' : 'belum_mulai'),
                    'progress_pct' => $progressVal * 100
                ]);
                $this->info("Created Phase: $name (Bobot: {$phaseBobotPct}%)");
            } else {
                // It's a Task under current Phase
                if ($currentPhase) {
                    $taskBobotPct = ($excelBobot / $currentPhaseExcelBobot) * 100;
                    
                    $taskStatus = 'belum_mulai';
                    if ($progressVal >= 1) $taskStatus = 'selesai';
                    elseif ($progressVal > 0) $taskStatus = 'in_progress';
                    
                    $task = Task::create([
                        'phase_id' => $currentPhase->id,
                        'nama_task' => $name,
                        'bobot_pct' => round($taskBobotPct, 2),
                        'status' => $taskStatus,
                        'progress_pct' => round($progressVal * 100, 2),
                        'prioritas' => 'medium'
                    ]);
                    $this->info("  Created Task: $name (Bobot: " . round($taskBobotPct, 2) . "%, Progress: " . round($progressVal * 100, 2) . "%)");
                }
            }
        }
        
        $progressService = app(\App\Services\ProgressService::class);
        $progressService->updateProjectProgress($project);

        $this->info("Data Excel berhasil dimigrasi!");
    }
}
