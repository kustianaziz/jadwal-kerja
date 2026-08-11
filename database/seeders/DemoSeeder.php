<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pic;
use App\Models\ProjectGroup;
use App\Models\Project;
use App\Models\Phase;
use App\Models\Task;
use App\Models\JournalEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // === USERS ===
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@tracker.pro',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@tracker.pro',
            'password' => Hash::make('password'),
            'role' => 'pm',
        ]);

        $dewi = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@tracker.pro',
            'password' => Hash::make('password'),
            'role' => 'pic',
        ]);

        $rudi = User::create([
            'name' => 'Rudi Hermawan',
            'email' => 'rudi@tracker.pro',
            'password' => Hash::make('password'),
            'role' => 'pic',
        ]);

        // === PICs ===
        $picAndi = Pic::create(['user_id' => $budi->id, 'nama' => 'Budi Santoso', 'email' => 'budi@tracker.pro', 'jabatan' => 'Project Manager', 'unit_kerja' => 'IT']);
        $picDewi = Pic::create(['user_id' => $dewi->id, 'nama' => 'Dewi Lestari', 'email' => 'dewi@tracker.pro', 'jabatan' => 'Frontend Developer', 'unit_kerja' => 'IT']);
        $picRudi = Pic::create(['user_id' => $rudi->id, 'nama' => 'Rudi Hermawan', 'email' => 'rudi@tracker.pro', 'jabatan' => 'Backend Developer', 'unit_kerja' => 'IT']);

        // === PROJECT GROUPS ===
        $grupAkademik = ProjectGroup::create(['nama_grup' => 'Sistem Akademik', 'deskripsi' => 'Proyek terkait sistem informasi akademik', 'created_by' => $admin->id]);
        $grupInternal = ProjectGroup::create(['nama_grup' => 'Tools Internal', 'deskripsi' => 'Proyek tools internal perusahaan', 'created_by' => $admin->id]);

        // === PROJECT 1: ERP ===
        $proj1 = Project::create([
            'group_id' => $grupAkademik->id,
            'nama_proyek' => 'ERP Universitas Terpadu',
            'deskripsi' => '<p>Pengembangan sistem ERP terintegrasi untuk manajemen akademik, keuangan, dan kepegawaian universitas.</p>',
            'pm_user_id' => $budi->id,
            'tanggal_mulai' => '2026-06-01',
            'target_selesai' => '2026-12-31',
            'prioritas' => 'high',
            'status' => 'berjalan',
            'progress_pct' => 0,
            'health_score' => 100,
            'health_status' => 'healthy',
        ]);

        // Fase 1: Asesmen (30%)
        $fase1 = Phase::create([
            'project_id' => $proj1->id, 'nama_fase' => 'Asesmen & Analisis', 'urutan' => 1,
            'bobot_pct' => 20, 'tanggal_mulai' => '2026-06-01', 'tanggal_target' => '2026-07-15',
            'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-07-14',
        ]);
        $t1 = Task::create(['phase_id' => $fase1->id, 'nama_task' => 'Pengumpulan Requirement', 'bobot_pct' => 40, 'tanggal_mulai' => '2026-06-01', 'deadline' => '2026-06-20', 'prioritas' => 'high', 'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-06-19']);
        $t1->pics()->attach($picDewi->id, ['peran' => 'utama']);
        $t2 = Task::create(['phase_id' => $fase1->id, 'nama_task' => 'Analisis Gap Sistem Existing', 'bobot_pct' => 30, 'tanggal_mulai' => '2026-06-15', 'deadline' => '2026-07-05', 'prioritas' => 'high', 'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-07-04']);
        $t2->pics()->attach($picRudi->id, ['peran' => 'utama']);
        $t3 = Task::create(['phase_id' => $fase1->id, 'nama_task' => 'Penyusunan Dokumen SRS', 'bobot_pct' => 30, 'tanggal_mulai' => '2026-07-01', 'deadline' => '2026-07-15', 'prioritas' => 'medium', 'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-07-14']);
        $t3->pics()->attach($picDewi->id, ['peran' => 'utama']);
        $t3->pics()->attach($picRudi->id, ['peran' => 'kontributor']);

        // Fase 2: Development (60%)
        $fase2 = Phase::create([
            'project_id' => $proj1->id, 'nama_fase' => 'Development', 'urutan' => 2,
            'bobot_pct' => 60, 'tanggal_mulai' => '2026-07-16', 'tanggal_target' => '2026-11-30',
            'status' => 'in_progress', 'progress_pct' => 0,
        ]);
        $t4 = Task::create(['phase_id' => $fase2->id, 'nama_task' => 'Setup Database & Arsitektur', 'bobot_pct' => 20, 'tanggal_mulai' => '2026-07-16', 'deadline' => '2026-08-01', 'prioritas' => 'high', 'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-07-30']);
        $t4->pics()->attach($picRudi->id, ['peran' => 'utama']);
        $t5 = Task::create(['phase_id' => $fase2->id, 'nama_task' => 'Backend API Core Modules', 'bobot_pct' => 40, 'tanggal_mulai' => '2026-08-01', 'deadline' => '2026-10-15', 'prioritas' => 'high', 'status' => 'in_progress', 'progress_pct' => 55]);
        $t5->pics()->attach($picRudi->id, ['peran' => 'utama']);
        $t6 = Task::create(['phase_id' => $fase2->id, 'nama_task' => 'Frontend UI & Integrasi', 'bobot_pct' => 25, 'tanggal_mulai' => '2026-08-15', 'deadline' => '2026-11-15', 'prioritas' => 'medium', 'status' => 'in_progress', 'progress_pct' => 30]);
        $t6->pics()->attach($picDewi->id, ['peran' => 'utama']);
        $t7 = Task::create(['phase_id' => $fase2->id, 'nama_task' => 'Unit Testing & Bug Fixing', 'bobot_pct' => 15, 'tanggal_mulai' => '2026-10-01', 'deadline' => '2026-11-30', 'prioritas' => 'medium', 'status' => 'belum_mulai', 'progress_pct' => 0]);
        $t7->pics()->attach($picRudi->id, ['peran' => 'utama']);
        $t7->pics()->attach($picDewi->id, ['peran' => 'kontributor']);

        // Fase 3: Deployment (20%)
        $fase3 = Phase::create([
            'project_id' => $proj1->id, 'nama_fase' => 'Testing & Deployment', 'urutan' => 3,
            'bobot_pct' => 20, 'tanggal_mulai' => '2026-12-01', 'tanggal_target' => '2026-12-31',
            'status' => 'belum_mulai', 'progress_pct' => 0,
        ]);
        Task::create(['phase_id' => $fase3->id, 'nama_task' => 'UAT (User Acceptance Testing)', 'bobot_pct' => 50, 'tanggal_mulai' => '2026-12-01', 'deadline' => '2026-12-15', 'prioritas' => 'high', 'status' => 'belum_mulai', 'progress_pct' => 0]);
        Task::create(['phase_id' => $fase3->id, 'nama_task' => 'Migrasi Data & Go-Live', 'bobot_pct' => 50, 'tanggal_mulai' => '2026-12-16', 'deadline' => '2026-12-31', 'prioritas' => 'high', 'status' => 'belum_mulai', 'progress_pct' => 0]);

        // === PROJECT 2: Asesmen Kinerja ===
        $proj2 = Project::create([
            'group_id' => $grupInternal->id,
            'nama_proyek' => 'Sistem Penilaian Kinerja Online',
            'deskripsi' => '<p>Platform untuk asesmen kinerja karyawan secara digital dengan metrik terukur.</p>',
            'pm_user_id' => $budi->id,
            'tanggal_mulai' => '2026-07-01',
            'target_selesai' => '2026-10-31',
            'prioritas' => 'medium',
            'status' => 'berjalan',
            'progress_pct' => 0,
            'health_score' => 100,
            'health_status' => 'healthy',
        ]);

        $fase2a = Phase::create([
            'project_id' => $proj2->id, 'nama_fase' => 'Perancangan', 'urutan' => 1,
            'bobot_pct' => 30, 'tanggal_mulai' => '2026-07-01', 'tanggal_target' => '2026-08-15',
            'status' => 'selesai', 'progress_pct' => 100, 'completed_at' => '2026-08-14',
        ]);
        Task::create(['phase_id' => $fase2a->id, 'nama_task' => 'Desain Form Penilaian', 'bobot_pct' => 50, 'prioritas' => 'high', 'status' => 'selesai', 'progress_pct' => 100, 'tanggal_mulai' => '2026-07-01', 'deadline' => '2026-07-31', 'completed_at' => '2026-07-30']);
        Task::create(['phase_id' => $fase2a->id, 'nama_task' => 'Desain Dashboard Rekapitulasi', 'bobot_pct' => 50, 'prioritas' => 'medium', 'status' => 'selesai', 'progress_pct' => 100, 'tanggal_mulai' => '2026-08-01', 'deadline' => '2026-08-15', 'completed_at' => '2026-08-14']);

        $fase2b = Phase::create([
            'project_id' => $proj2->id, 'nama_fase' => 'Implementasi', 'urutan' => 2,
            'bobot_pct' => 50, 'tanggal_mulai' => '2026-08-16', 'tanggal_target' => '2026-10-15',
            'status' => 'in_progress', 'progress_pct' => 0,
        ]);
        Task::create(['phase_id' => $fase2b->id, 'nama_task' => 'Modul Input Penilaian', 'bobot_pct' => 40, 'prioritas' => 'high', 'status' => 'in_progress', 'progress_pct' => 40, 'tanggal_mulai' => '2026-08-16', 'deadline' => '2026-09-15']);
        Task::create(['phase_id' => $fase2b->id, 'nama_task' => 'Modul Laporan & Grafik', 'bobot_pct' => 35, 'prioritas' => 'medium', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-09-16', 'deadline' => '2026-10-01']);
        Task::create(['phase_id' => $fase2b->id, 'nama_task' => 'Integrasi SSO', 'bobot_pct' => 25, 'prioritas' => 'medium', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-10-01', 'deadline' => '2026-10-15']);

        $fase2c = Phase::create([
            'project_id' => $proj2->id, 'nama_fase' => 'Go-Live', 'urutan' => 3,
            'bobot_pct' => 20, 'tanggal_mulai' => '2026-10-16', 'tanggal_target' => '2026-10-31',
            'status' => 'belum_mulai', 'progress_pct' => 0,
        ]);
        Task::create(['phase_id' => $fase2c->id, 'nama_task' => 'Deployment & Training', 'bobot_pct' => 100, 'prioritas' => 'high', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-10-16', 'deadline' => '2026-10-31']);

        // === PROJECT 3: Website Portofolio ===
        $proj3 = Project::create([
            'group_id' => $grupInternal->id,
            'nama_proyek' => 'Redesign Website Portofolio',
            'deskripsi' => '<p>Redesign total website perusahaan untuk menampilkan portofolio proyek.</p>',
            'pm_user_id' => $budi->id,
            'tanggal_mulai' => '2026-08-01',
            'target_selesai' => '2026-09-30',
            'prioritas' => 'low',
            'status' => 'berjalan',
            'progress_pct' => 0,
            'health_score' => 100,
            'health_status' => 'healthy',
        ]);

        $fase3a = Phase::create([
            'project_id' => $proj3->id, 'nama_fase' => 'Desain Visual', 'urutan' => 1,
            'bobot_pct' => 40, 'tanggal_mulai' => '2026-08-01', 'tanggal_target' => '2026-08-31',
            'status' => 'in_progress', 'progress_pct' => 0,
        ]);
        Task::create(['phase_id' => $fase3a->id, 'nama_task' => 'Mockup Homepage', 'bobot_pct' => 60, 'prioritas' => 'high', 'status' => 'in_progress', 'progress_pct' => 70, 'tanggal_mulai' => '2026-08-01', 'deadline' => '2026-08-15']);
        Task::create(['phase_id' => $fase3a->id, 'nama_task' => 'Mockup Halaman Portofolio', 'bobot_pct' => 40, 'prioritas' => 'medium', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-08-16', 'deadline' => '2026-08-31']);

        $fase3b = Phase::create([
            'project_id' => $proj3->id, 'nama_fase' => 'Slicing & Coding', 'urutan' => 2,
            'bobot_pct' => 60, 'tanggal_mulai' => '2026-09-01', 'tanggal_target' => '2026-09-30',
            'status' => 'belum_mulai', 'progress_pct' => 0,
        ]);
        Task::create(['phase_id' => $fase3b->id, 'nama_task' => 'Slicing HTML/CSS', 'bobot_pct' => 50, 'prioritas' => 'high', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-09-01', 'deadline' => '2026-09-15']);
        Task::create(['phase_id' => $fase3b->id, 'nama_task' => 'CMS Integration', 'bobot_pct' => 50, 'prioritas' => 'medium', 'status' => 'belum_mulai', 'progress_pct' => 0, 'tanggal_mulai' => '2026-09-16', 'deadline' => '2026-09-30']);

        // === RECALCULATE ALL PROGRESS ===
        $progressService = app(\App\Services\ProgressService::class);
        
        // Recalculate phases
        foreach (Phase::all() as $phase) {
            $progressService->updatePhaseProgress($phase);
        }
        // Recalculate projects (also triggers health score)
        foreach (Project::all() as $project) {
            $progressService->updateProjectProgress($project);
            $progressService->validateBobotSeimbang($project);
        }

        // === JOURNAL ENTRIES ===
        JournalEntry::create([
            'project_id' => $proj1->id, 'phase_id' => $fase1->id,
            'tipe' => 'pencapaian', 'judul' => 'Fase Asesmen Selesai',
            'detail' => '<p>Seluruh dokumen requirement dan analisis gap telah diselesaikan tepat waktu. Siap lanjut ke fase Development.</p>',
            'created_by' => $budi->id,
        ]);
        JournalEntry::create([
            'project_id' => $proj1->id, 'phase_id' => $fase2->id, 'task_id' => $t5->id,
            'tipe' => 'update', 'judul' => 'Progress Backend API',
            'detail' => '<p>Modul akademik dan keuangan sudah 55% selesai. Modul kepegawaian masih dalam tahap perancangan endpoint.</p>',
            'created_by' => $rudi->id,
        ]);
        JournalEntry::create([
            'project_id' => $proj1->id, 'phase_id' => $fase2->id, 'task_id' => $t6->id,
            'tipe' => 'issue', 'judul' => 'Kendala Integrasi UI dengan API',
            'detail' => '<p>Terdapat ketidaksesuaian format response API dengan komponen frontend. Perlu koordinasi ulang format JSON.</p>',
            'created_by' => $dewi->id,
        ]);
    }
}
