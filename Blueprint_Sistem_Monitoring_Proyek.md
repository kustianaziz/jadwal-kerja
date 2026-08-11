# Blueprint Sistem Monitoring & Manajemen Proyek
### Project Tracker Pro — Pengembangan Lanjutan dari Dashboard Monitoring (DM) Existing

---

## 1. Latar Belakang & Tujuan

Tools internal saat ini (DM) sudah punya fondasi yang baik: Dashboard ringkasan (Total Aktif, Healthy, Attention, At Risk, Critical, Selesai), Progress Timeline bergaya Gantt, Grup Proyek, dan Jurnal Aktivitas per tahapan.

Yang masih perlu diperdalam:

1. **Detail task** — saat ini baru sebatas "Tahapan Proyek" (fase) dengan progress manual per fase. Belum ada struktur task/subtask dengan PIC, start date, end date, dan instruksi kerja yang jelas.
2. **Instruksi kerja** — belum ada deskripsi kaya (WYSIWYG) dan lampiran file per task.
3. **Progress otomatis berbobot** — ini titik terlemah: progress proyek (mis. ERP Umum 40%, UNJAYA 65%) tampaknya diinput manual atau dirata-rata sama besar per fase, padahal tiap fase/task punya bobot pekerjaan yang berbeda-beda (misalnya "Development" jauh lebih berat dari "Asesmen").

Tujuan blueprint ini: mendesain struktur data dan fitur sehingga **progress proyek dihitung otomatis dari bawah ke atas (bottom-up)** berdasarkan bobot dan status task riil — bukan angka yang diketik manual oleh admin.

---

## 2. Struktur Data (Entity Model)

```
Organisasi
 └─ Grup Proyek (opsional, mis. "Grup Proyek UNJAYA")
     └─ Proyek (mis. ERP Umum, Kerjasama, Perpustakaan)
         └─ Fase / Milestone (mis. Development, Asesmen)
             └─ Task
                 └─ Subtask (opsional, rekursif)
                     └─ Checklist Item (opsional, level terkecil)

Entitas pendukung:
- User (Admin, Project Manager, PIC/Anggota, Viewer)
- PIC (bisa multi-PIC per task: 1 Penanggung Jawab + n Kontributor)
- Deskripsi/Instruksi (WYSIWYG, rich text)
- Lampiran (file, link)
- Komentar / Jurnal Aktivitas
- Riwayat Perubahan (audit log)
- Notifikasi
```

### Skema field per level

| Entitas | Field Utama |
|---|---|
| **Proyek** | Nama, Grup, Deskripsi (WYSIWYG), Tanggal Mulai, Target Selesai, PM (Project Manager), Status Kesehatan (auto), % Progress (auto), Prioritas, Tag/Kategori |
| **Fase/Milestone** | Nama, Urutan, Bobot (%), Tanggal Mulai, Target Selesai, PIC Fase, Status (Belum Mulai/In Progress/Selesai/Terlambat) |
| **Task** | Nama, Fase induk, Bobot (%) dalam fase, PIC Utama, Kontributor (multi), Tanggal Mulai, Deadline, Estimasi Effort (jam/hari, opsional), Prioritas, Status, Deskripsi WYSIWYG, Lampiran, Dependency (task prasyarat), % Progress |
| **Subtask/Checklist** | Nama, Bobot (opsional, default sama rata), Selesai/Belum, PIC (opsional) |

---

## 3. Modul Fitur

### 3.1 Manajemen Proyek & Grup
- Tetap seperti existing (Buat Grup, Buat Proyek) — tambahkan field **PM (Project Manager)** terpisah dari PIC teknis, dan **Prioritas Proyek** (Low/Medium/High/Urgent) untuk membantu PM fokus.

### 3.2 Timeline (Gantt)
- Pertahankan visual timeline existing, tapi break down hingga level **task**, bukan hanya fase. PM bisa expand fase → lihat task → lihat subtask.
- Tambahkan garis **dependency** antar task (task B tidak bisa mulai sebelum task A selesai) — penting untuk critical path.

### 3.3 Task Management (Detail Rinci)
Form "Buat Task Baru" berisi:
- Nama Task *
- Fase/Milestone induk *
- PIC Utama * (dropdown dari Master PIC existing)
- Kontributor tambahan (multi-select, opsional)
- Tanggal Mulai * / Deadline *
- Bobot Task (%) dalam fase — lihat bagian 4
- Prioritas (Low/Medium/High)
- Status (Belum Mulai / In Progress / Review / Selesai / Blocked)
- Deskripsi Instruksi (**WYSIWYG** — lihat 3.4)
- Lampiran File (**lihat 3.4**)
- Dependency (task lain yang harus selesai dulu, opsional)
- Checklist/Subtask (opsional, untuk breakdown lebih kecil)

### 3.4 Deskripsi WYSIWYG & Lampiran
- **Editor rich text** (bold, italic, bullet/numbered list, heading, link, blockquote, embed gambar) — dipakai konsisten di: Deskripsi Proyek, Deskripsi Fase, Instruksi Task, dan Jurnal Aktivitas (existing "Tulis Jurnal" saat ini masih plain textarea).
- **Lampiran file**: upload multi-file (dokumen, gambar, spreadsheet), preview thumbnail, versioning (jika file direvisi, riwayat versi tersimpan), dan opsi tempel link eksternal (Google Drive/Figma/repo).

### 3.5 Sistem Pembobotan & Progress Otomatis (Inti Sistem)
Dijelaskan detail di Bagian 4.

### 3.6 Health Score Otomatis
Dipertahankan dan diperkuat — dijelaskan di Bagian 5.

### 3.7 Jurnal Aktivitas / Log
- Pertahankan struktur existing (Tipe: Update/Pencapaian, Judul, Detail), tambahkan:
  - Auto-log sistem (task diselesaikan, bobot diubah, deadline digeser) supaya jurnal tidak hanya manual.
  - Opsi lampirkan file pada jurnal.
  - Mention PIC lain (notifikasi).

### 3.8 Insight Otomatis
- Pertahankan panel "Insight Otomatis" existing, perluas aturan, contoh:
  - Task dengan bobot besar (>20%) tapi progress 0% dan mendekati deadline → prioritas warning tertinggi.
  - Proyek dengan progress aktual < progress ekspektasi (berdasarkan waktu berjalan) → auto masuk kategori Attention/At Risk.
  - PIC yang memegang >X task overdue sekaligus → flag beban kerja.

### 3.9 Dashboard & Reporting
- Tambahkan filter per PM/PIC, per Grup, per rentang tanggal.
- Tambahkan **laporan mingguan otomatis** (export PDF/Excel) berisi ringkasan progress, task overdue, dan proyek berisiko — untuk dikirim ke stakeholder.

### 3.10 Notifikasi & Reminder
- Reminder H-3 dan H-1 sebelum deadline task.
- Notifikasi saat task ditandai Blocked atau saat bobot proyek tidak seimbang (lihat 4.3).
- Notifikasi saat health score turun kategori (mis. Healthy → Attention).

### 3.11 Role & Permission
| Role | Akses |
|---|---|
| Admin | Full akses semua proyek & pengaturan sistem |
| Project Manager | Kelola proyek yang di-assign, atur bobot, lihat semua laporan |
| PIC / Anggota | Update progress task miliknya, tulis jurnal, upload lampiran |
| Viewer/Stakeholder | Read-only dashboard & timeline |

---

## 4. Mekanisme Pembobotan & Progress Otomatis

Ini adalah jawaban langsung untuk kebutuhan utama Anda: **progress harus sesuai bobot pekerjaan**, bukan rata-rata polos.

### 4.1 Prinsip Dasar
Setiap level (Task dalam Fase, Fase dalam Proyek) diberi **bobot (%)** yang mencerminkan besar/pentingnya pekerjaan tersebut. Progress level di atasnya dihitung otomatis dari kontribusi berbobot level di bawahnya — dihitung **bottom-up**, sehingga admin/PM tidak lagi mengetik manual angka progress proyek.

### 4.2 Formula

**Progress Task** (jika punya subtask/checklist):
```
Progress_Task = Σ (Bobot_Subtask_i × Progress_Subtask_i)  /  Σ Bobot_Subtask_i
```
Jika tidak ada subtask, progress task diinput langsung oleh PIC (0–100%), atau otomatis 100% saat status diubah "Selesai".

**Progress Fase**:
```
Progress_Fase = Σ (Bobot_Task_i × Progress_Task_i)  /  Σ Bobot_Task_i
```

**Progress Proyek**:
```
Progress_Proyek = Σ (Bobot_Fase_i × Progress_Fase_i)  /  Σ Bobot_Fase_i
```

Karena tiap penyebut dinormalisasi, sistem tetap valid meski total bobot per level dipaksa = 100%.

### 4.3 Aturan Input Bobot
- Total bobot seluruh task **dalam satu fase** harus = 100%. Sama untuk total bobot seluruh fase dalam satu proyek.
- Saat PM menambah/menghapus task, sistem menampilkan **sisa bobot yang tersedia** secara real-time, dan tombol **"Auto-distribusi rata"** untuk membagi sisa bobot secara otomatis ke task yang belum diisi.
- Jika total bobot ≠ 100% saat proyek disimpan → sistem menampilkan warning (bukan blocking keras, agar draft tetap bisa disimpan), dan proyek tersebut ditandai "Bobot belum seimbang" di dashboard.
- Bobot bisa direvisi kapan saja oleh PM (mis. saat scope berubah); setiap perubahan bobot tercatat di Riwayat Perubahan agar progress historis tetap bisa ditelusuri (audit trail), bukan tiba-tiba melompat tanpa penjelasan.

### 4.4 Contoh Perhitungan

Fase "Development" (bobot 60% dari proyek), berisi 3 task:

| Task | Bobot dalam Fase | Progress Task |
|---|---|---|
| Setup Database | 30% | 100% |
| Backend API | 50% | 60% |
| Frontend UI | 20% | 20% |

```
Progress_Fase Development = (30%×100%) + (50%×60%) + (20%×20%)
                           = 30 + 30 + 4 = 64%
```

Jika proyek punya 2 fase — Development (bobot 60%, progress 64%) dan Asesmen (bobot 40%, progress 100%, sudah selesai):

```
Progress_Proyek = (60%×64%) + (40%×100%) = 38.4% + 40% = 78.4%
```

Dengan cara ini, penyelesaian task-task kecil yang bobotnya besar akan mendorong progress proyek jauh lebih signifikan dibanding task remeh — sesuai realita pekerjaan.

### 4.5 Cara PIC Update Progress
- **Task tanpa subtask**: PIC mengubah status (In Progress → set manual % atau slider), atau langsung "Selesai" = 100%.
- **Task dengan checklist**: progress otomatis dari jumlah item tercentang (bisa diberi bobot berbeda per item jika perlu presisi tinggi, default sama rata).
- Setiap update progress otomatis membuat entri di Jurnal Aktivitas (auto-log), sehingga histori "kapan progress berubah dari berapa ke berapa" selalu terekam.

---

## 5. Mekanisme Health Score Otomatis (Diperkuat)

Health score existing (0–100, kategori Healthy/Attention/At Risk/Critical) diperkuat dengan formula gabungan beberapa faktor:

| Faktor | Bobot Pengaruh | Cara Hitung |
|---|---|---|
| **Schedule Variance** | 40% | Progress aktual vs progress ekspektasi (berdasarkan % waktu proyek yang sudah berlalu terhadap total durasi) |
| **Task Terlambat (Overdue)** | 30% | Jumlah & bobot task yang melewati deadline tapi belum selesai |
| **Aktivitas Terbaru** | 15% | Berapa lama sejak update/jurnal terakhir (proyek "sunyi" = risiko) |
| **Dependency Terblokir** | 15% | Ada task berstatus Blocked yang menghambat task lain |

Kategori (contoh threshold, bisa disesuaikan):
- **Healthy**: skor ≥ 80
- **Attention**: 60–79
- **At Risk**: 35–59
- **Critical**: < 35

Insight otomatis (Bagian 3.8) diturunkan langsung dari faktor-faktor ini, sehingga narasinya bisa lebih spesifik, misalnya: *"Task 'Backend API' (bobot 50% dari fase Development) terlambat 4 hari dan belum ada update 6 hari — ini penyebab utama status Attention pada proyek ERP Umum."*

---

## 6. Alur Penggunaan (User Flow) untuk PM

1. PM membuat **Proyek** → isi info dasar, target selesai, PM penanggung jawab.
2. PM membuat **Fase/Milestone** → tentukan urutan & bobot tiap fase (total 100%).
3. Di tiap fase, PM/PIC menambahkan **Task** → isi PIC, tanggal mulai/selesai, bobot dalam fase, instruksi (WYSIWYG + lampiran).
4. (Opsional) Task besar dipecah jadi **Subtask/Checklist**.
5. PIC mengerjakan → update status & progress task secara berkala, tulis jurnal update.
6. Sistem otomatis menghitung ulang **progress fase → progress proyek → health score** setiap ada perubahan.
7. PM memantau dari **Dashboard**: proyek mana Attention/At Risk, task mana overdue, insight otomatis, lalu ambil tindakan (reassign PIC, geser deadline, dsb).
8. Laporan mingguan otomatis ter-generate untuk stakeholder.

---

## 7. Penambahan pada UI Existing (Ringkas)

| Layar Existing | Penambahan yang Diperlukan |
|---|---|
| Buat Proyek Baru | Hapus/ubah field "Progress Awal (%)" manual → ganti jadi read-only "dihitung otomatis dari task", tambah field PM |
| Panel Detail Proyek (Tahapan Proyek) | Ubah "Tahapan Proyek" jadi hierarki Fase → Task → Subtask, tiap level punya kolom Bobot, PIC, Start/Deadline |
| Tulis Jurnal Aktivitas | Ganti textarea Detail jadi WYSIWYG editor, tambah opsi lampirkan file & mention PIC |
| Tambah Fase Baru | Tambahkan field Bobot (%) dengan indikator sisa bobot tersedia |
| Progress Timeline | Tambahkan level expand hingga Task, indikator dependency antar-task |

---

## 8. Skema Database (MySQL)

### 8.1 Diagram Relasi (ringkas)

```
users ──────< task_pics >────── tasks ──────< checklist_items
  │                                │  │
  │                                │  └──< task_dependencies (self-ref)
  │                                │
  pics ──< phase_pic               phases ──< journal_entries
  │                                │              │
  └──< projects (pm_user_id)       │              └──< attachments (polymorphic)
                                   │
groups ──< projects ──< phases ──< tasks ──< attachments (polymorphic)
projects ──< journal_entries
projects/phases/tasks ──< audit_logs (polymorphic)
users ──< notifications
```

### 8.2 DDL Lengkap

```sql
-- =========================================================
-- 1. USERS & PIC
-- =========================================================
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('admin','pm','pic','viewer') NOT NULL DEFAULT 'pic',
    avatar_url      VARCHAR(255) NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pics (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NULL,          -- boleh NULL jika PIC belum punya akun login
    nama            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NULL,
    telepon         VARCHAR(30) NULL,
    jabatan         VARCHAR(100) NULL,
    unit_kerja      VARCHAR(100) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pics_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 2. GRUP & PROYEK
-- =========================================================
CREATE TABLE project_groups (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_grup       VARCHAR(150) NOT NULL,
    deskripsi       TEXT NULL,
    target_selesai  DATE NULL,
    created_by      BIGINT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_group_creator FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE projects (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id        BIGINT UNSIGNED NULL,
    nama_proyek     VARCHAR(150) NOT NULL,
    deskripsi       MEDIUMTEXT NULL,                -- HTML dari WYSIWYG
    pm_user_id      BIGINT UNSIGNED NULL,           -- Project Manager penanggung jawab
    tanggal_mulai   DATE NULL,
    target_selesai  DATE NULL,
    prioritas       ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    status          ENUM('draft','berjalan','selesai','dibatalkan') NOT NULL DEFAULT 'berjalan',
    progress_pct    DECIMAL(5,2) NOT NULL DEFAULT 0.00,   -- CACHE hasil kalkulasi bottom-up
    health_score    TINYINT UNSIGNED NOT NULL DEFAULT 100, -- CACHE 0-100
    health_status   ENUM('healthy','attention','at_risk','critical') NOT NULL DEFAULT 'healthy',
    is_bobot_seimbang TINYINT(1) NOT NULL DEFAULT 1,  -- flag validasi total bobot fase = 100%
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at    DATETIME NULL,
    CONSTRAINT fk_project_group FOREIGN KEY (group_id) REFERENCES project_groups(id) ON DELETE SET NULL,
    CONSTRAINT fk_project_pm FOREIGN KEY (pm_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project_status (status),
    INDEX idx_project_health (health_status)
) ENGINE=InnoDB;

-- =========================================================
-- 3. FASE / MILESTONE
-- =========================================================
CREATE TABLE phases (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      BIGINT UNSIGNED NOT NULL,
    nama_fase       VARCHAR(150) NOT NULL,
    deskripsi       MEDIUMTEXT NULL,
    urutan          SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    bobot_pct       DECIMAL(5,2) NOT NULL DEFAULT 0.00,   -- bobot fase dalam proyek (total per proyek = 100)
    pic_id          BIGINT UNSIGNED NULL,
    tanggal_mulai   DATE NULL,
    tanggal_target  DATE NULL,
    status          ENUM('belum_mulai','in_progress','review','selesai','terlambat') NOT NULL DEFAULT 'belum_mulai',
    progress_pct    DECIMAL(5,2) NOT NULL DEFAULT 0.00,   -- CACHE dari task-task di dalamnya
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at    DATETIME NULL,
    CONSTRAINT fk_phase_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_phase_pic FOREIGN KEY (pic_id) REFERENCES pics(id) ON DELETE SET NULL,
    INDEX idx_phase_project (project_id)
) ENGINE=InnoDB;

-- =========================================================
-- 4. TASK (mendukung subtask via parent_task_id)
-- =========================================================
CREATE TABLE tasks (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phase_id            BIGINT UNSIGNED NOT NULL,
    parent_task_id      BIGINT UNSIGNED NULL,      -- NULL = task level atas; diisi = subtask
    nama_task           VARCHAR(200) NOT NULL,
    deskripsi           MEDIUMTEXT NULL,            -- HTML dari WYSIWYG (instruksi kerja)
    bobot_pct           DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- bobot dalam fase (atau dalam parent task jika subtask)
    tanggal_mulai       DATE NULL,
    deadline            DATE NULL,
    estimasi_effort_jam DECIMAL(6,2) NULL,
    prioritas           ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status              ENUM('belum_mulai','in_progress','review','blocked','selesai') NOT NULL DEFAULT 'belum_mulai',
    progress_pct        DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- manual ATAU cache dari checklist/subtask
    progress_source     ENUM('manual','checklist','subtask') NOT NULL DEFAULT 'manual',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at        DATETIME NULL,
    CONSTRAINT fk_task_phase FOREIGN KEY (phase_id) REFERENCES phases(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_parent FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    INDEX idx_task_phase (phase_id),
    INDEX idx_task_parent (parent_task_id),
    INDEX idx_task_status (status),
    INDEX idx_task_deadline (deadline)
) ENGINE=InnoDB;

-- PIC per task: 1 utama + n kontributor
CREATE TABLE task_pics (
    task_id     BIGINT UNSIGNED NOT NULL,
    pic_id      BIGINT UNSIGNED NOT NULL,
    peran       ENUM('utama','kontributor') NOT NULL DEFAULT 'kontributor',
    PRIMARY KEY (task_id, pic_id),
    CONSTRAINT fk_taskpic_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_taskpic_pic  FOREIGN KEY (pic_id)  REFERENCES pics(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dependency antar task (task tidak bisa mulai sebelum task lain selesai)
CREATE TABLE task_dependencies (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id             BIGINT UNSIGNED NOT NULL,
    depends_on_task_id  BIGINT UNSIGNED NOT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dep_task   FOREIGN KEY (task_id)            REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_dep_on     FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    UNIQUE KEY uq_dependency (task_id, depends_on_task_id)
) ENGINE=InnoDB;

-- Checklist item (level terkecil, opsional bobot per item)
CREATE TABLE checklist_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id         BIGINT UNSIGNED NOT NULL,
    nama_item       VARCHAR(255) NOT NULL,
    bobot_pct       DECIMAL(5,2) NOT NULL DEFAULT 0.00,  -- default dibagi rata oleh aplikasi
    is_done         TINYINT(1) NOT NULL DEFAULT 0,
    urutan          SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_checklist_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 5. LAMPIRAN (polymorphic: project / phase / task / journal)
-- =========================================================
CREATE TABLE attachments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attachable_type ENUM('project','phase','task','journal') NOT NULL,
    attachable_id   BIGINT UNSIGNED NOT NULL,
    nama_file       VARCHAR(255) NOT NULL,
    path_file       VARCHAR(500) NOT NULL,
    ukuran_bytes    BIGINT UNSIGNED NULL,
    mime_type       VARCHAR(100) NULL,
    versi           SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    uploaded_by     BIGINT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attachment_user FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_attachable (attachable_type, attachable_id)
) ENGINE=InnoDB;

-- =========================================================
-- 6. JURNAL AKTIVITAS
-- =========================================================
CREATE TABLE journal_entries (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      BIGINT UNSIGNED NOT NULL,
    phase_id        BIGINT UNSIGNED NULL,
    task_id         BIGINT UNSIGNED NULL,
    tipe            ENUM('update','pencapaian','issue','system') NOT NULL DEFAULT 'update',
    judul           VARCHAR(200) NOT NULL,
    detail          MEDIUMTEXT NULL,          -- HTML dari WYSIWYG
    created_by      BIGINT UNSIGNED NOT NULL, -- created_by = system_user_id untuk auto-log
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_journal_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_journal_phase   FOREIGN KEY (phase_id)   REFERENCES phases(id)   ON DELETE SET NULL,
    CONSTRAINT fk_journal_task    FOREIGN KEY (task_id)    REFERENCES tasks(id)    ON DELETE SET NULL,
    CONSTRAINT fk_journal_user    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_journal_project (project_id)
) ENGINE=InnoDB;

CREATE TABLE journal_mentions (
    journal_id  BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (journal_id, user_id),
    CONSTRAINT fk_mention_journal FOREIGN KEY (journal_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    CONSTRAINT fk_mention_user    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 7. NOTIFIKASI & AUDIT LOG
-- =========================================================
CREATE TABLE notifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    tipe            ENUM('deadline','bobot_tidak_seimbang','health_turun','mention','blocked') NOT NULL,
    judul           VARCHAR(200) NOT NULL,
    pesan           VARCHAR(500) NOT NULL,
    related_type    ENUM('project','phase','task') NULL,
    related_id      BIGINT UNSIGNED NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user_unread (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type     ENUM('project','phase','task') NOT NULL,
    entity_id       BIGINT UNSIGNED NOT NULL,
    field_changed   VARCHAR(100) NOT NULL,     -- mis. 'bobot_pct', 'status', 'deadline'
    old_value       VARCHAR(255) NULL,
    new_value       VARCHAR(255) NULL,
    changed_by      BIGINT UNSIGNED NOT NULL,
    changed_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB;
```

### 8.3 Catatan Implementasi Kalkulasi Progress

- Kolom `progress_pct` dan `health_score`/`health_status` pada `projects` dan `progress_pct` pada `phases` adalah **kolom cache**, bukan sumber kebenaran. Sumber kebenaran adalah `progress_pct` di level `tasks`/`checklist_items`.
- Rekalkulasi dilakukan di **application layer / service** (bukan trigger MySQL murni) supaya formula bottom-up (Bagian 4) mudah diaudit dan diuji — dipicu setiap kali ada `UPDATE`/`INSERT`/`DELETE` pada `tasks` atau `checklist_items`:
  1. Hitung ulang `progress_pct` task induk (jika ada checklist/subtask).
  2. Hitung ulang `progress_pct` fase terkait (`phases.id = tasks.phase_id`).
  3. Hitung ulang `progress_pct` proyek (`projects.id`).
  4. Hitung ulang `health_score`/`health_status` proyek berdasarkan formula Bagian 5.
  5. Tulis entri `audit_logs` bila field yang berubah relevan (bobot, status, tanggal).
- Validasi total bobot (`SUM(bobot_pct)` per `phase_id` dalam satu `project_id`, dan per `phase_id` untuk task-task-nya) dijalankan di service layer saat create/update, hasilnya disimpan ke `projects.is_bobot_seimbang` agar dashboard bisa langsung query tanpa hitung ulang tiap saat.

## 9. Rekomendasi Teknis (Opsional)

- **Perhitungan progress**: sebaiknya dihitung di **backend** (service saat ada perubahan task), bukan di frontend, agar konsisten di semua tampilan (dashboard, laporan, API) — lihat 8.3.
- **Audit log** terpisah dari Jurnal Aktivitas: log sistem (perubahan bobot, status, tanggal) vs jurnal naratif (cerita/update manual dari PIC).
- **WYSIWYG**: gunakan editor berbasis rich-text yang menyimpan output sebagai HTML terstruktur (kolom `MEDIUMTEXT`) agar formatting konsisten saat ditampilkan ulang.
- **Stack contoh**: Laravel/Node(NestJS) + MySQL 8 untuk backend (mendukung `JSON`, CTE `WITH RECURSIVE` untuk query subtask berjenjang), React/Vue di frontend, editor WYSIWYG seperti TipTap/Quill.

## 10. UI/UX Design System — Gaya Skeuomorphism

Skeuomorphism cocok untuk tools monitoring seperti ini karena elemen seperti **gauge/meter, kartu index, folder, dan stempel "Selesai"** punya asosiasi visual yang langsung dikenali PM — mengurangi beban kognitif dibanding ikon flat abstrak.

### 10.1 Token Desain

**Warna** (palet material dunia-nyata, bukan flat-UI):

| Token | Hex | Penggunaan |
|---|---|---|
| `--paper-cream` | `#F3ECDD` | Latar utama (kertas/manila folder) |
| `--leather-brown` | `#6B4A34` | Sidebar, header, elemen "binder" |
| `--brushed-steel` | `#B9BEC7` | Frame kartu, border metalik |
| `--ink-navy` | `#232B3A` | Teks utama (tinta) |
| `--brass-gold` | `#C7A253` | Aksen, ikon "logam", garis pembatas dekoratif |
| `--gauge-green` | `#3C8B5C` | Healthy (jarum hijau di gauge) |
| `--gauge-amber` | `#D89A2B` | Attention |
| `--gauge-orange` | `#C96A2E` | At Risk |
| `--gauge-red` | `#B3402E` | Critical |

**Tipografi**:
- Display/Heading: slab-serif tebal dengan efek *emboss/letterpress* tipis (mis. Roboto Slab / Zilla Slab) — kesan "dicap" di kertas/kulit.
- Body/Data: sans-serif humanis netral (mis. Inter/Source Sans) untuk keterbacaan angka & tabel.
- Angka besar (progress %, health score): tabular numerals dengan sedikit *inner shadow* seperti angka digital pada dial jam mekanik.

**Efek permukaan (signature skeuomorphism)**:
- Kartu (`card`): gradient halus terang→gelap (atas ke bawah), `box-shadow` ganda — satu shadow lembut di luar (mengangkat kartu dari latar), satu *inset highlight* tipis di tepi atas (efek permukaan timbul).
- Tombol utama: gradient vertikal + `inset` highlight di atas dan `inset` shadow di bawah saat *pressed* (efek ditekan seperti tombol fisik).
- Progress bar bobot: dirender sebagai **gauge/thermometer** kaca dengan bevel logam di tepi, cairan/mercury mengisi sesuai persentase, dengan **garis-garis pembagi (tick mark)** yang merepresentasikan segmen bobot tiap task — bukan bar polos.

### 10.2 Spesifikasi Komponen Kunci

| Komponen | Perlakuan Skeuomorphism |
|---|---|
| **Sidebar navigasi** | Tab bergaya "map folder" bertumpuk (Dashboard/Pengguna/Master PIC seperti label folder fisik menonjol saat aktif) |
| **Kartu ringkasan (Total Aktif/Healthy/dst)** | Kartu kertas timbul dengan ikon 3D mini (koper kulit utk "Total Aktif", meteran gauge utk "Health") |
| **Health Score** | **Speedometer/dial analog** dengan jarum (needle) menunjuk ke skor 0–100, warna zona hijau→merah di sekeliling dial, kaca reflektif tipis di atasnya |
| **Progress Timeline (Gantt)** | Baris seperti pita ukur (measuring tape) dengan tekstur garis mm; due date ditandai bendera kecil 3D (bukan diamond flat) |
| **Progress Bar Task berbobot** | Tabung gauge kaca vertikal/horizontal dgn cairan berwarna sesuai status, tick mark = pembagian bobot antar task dalam fase |
| **Tombol "Buat Proyek" / "Simpan"** | Tombol glossy dengan highlight cembung di atas, sedikit cekung saat ditekan (active state), drop shadow lembut |
| **Modal (Buat Grup/Proyek/Tulis Jurnal)** | Kartu seperti kertas formulir dengan sudut terlipat halus (folded-corner shadow) di pojok kanan atas |
| **Editor WYSIWYG (deskripsi/instruksi)** | Area seperti notepad bergaris tipis (ruled lines), toolbar seperti panel alat tulis (ikon pena, klip kertas utk lampiran) |
| **Lampiran file** | Ikon dokumen 3D dengan bayangan tumpuk (efek kertas ditumpuk), klip logam kecil di sudut sebagai tombol unduh |
| **Status "Selesai"** | Efek **stempel karet (rubber stamp)** miring dengan tekstur tinta sedikit pudar saat task ditandai selesai — micro-interaction klik disertai animasi "cap" turun |
| **Checklist item** | Kotak centang seperti kertas dengan tanda centang tulisan tangan (bukan ikon flat) |
| **PIC Avatar** | Bingkai foto seperti pin/badge nama dada (name tag) dengan sedikit shadow menggantung |

### 10.3 Layout Kunci (deskripsi + wireframe ringkas)

**A. Dashboard** — tetap seperti existing (kartu ringkasan atas, insight panel, timeline bawah), tapi tiap kartu ringkasan diberi efek kertas timbul + ikon 3D, dan panel Insight Otomatis dibingkai seperti "memo kuning ditempel" (sticky note miring sedikit).

**B. Detail Proyek / Task Panel (sisi kanan, seperti Image 4)**
```
┌───────────────────────────────┐
│  🗂  ERP UMUM        [≡ menu] │  ← header seperti label folder kulit
│  ────────────────────────────  │
│   ◔ GAUGE ANALOG               │  ← health score sebagai dial jarum
│   Progress: [====|||    ] 40%  │  ← gauge kaca bertik sesuai bobot fase
│  ────────────────────────────  │
│  Tahapan Proyek                │
│   • Development   [In Progress]│  ← pita status seperti label kertas
│     Bobot: 60% | Progress: 64% │
│   ✔ Asesmen  (STEMPEL SELESAI) │
│  ────────────────────────────  │
│  📎 Lampiran   📓 Jurnal        │  ← tab seperti map arsip
└───────────────────────────────┘
```

**C. Form Buat Task** — dirender seperti formulir kertas dengan garis-garis pembatas antar section, field bobot menampilkan **sisa bobot tersedia** sebagai indikator gauge kecil di samping input (mis. lingkaran arloji mini yang terisi seiring bobot ditambahkan oleh task-task lain).

### 10.4 Prinsip Interaksi & Aksesibilitas

- Setiap efek 3D/tekstur **tidak boleh mengorbankan kontras teks** — teks tetap memenuhi rasio kontras WCAG AA meski di atas tekstur kertas/kulit.
- Motion tetap disiplin: animasi "tombol ditekan", "stempel selesai", "jarum gauge bergerak" adalah animasi *purposeful* (memberi feedback), bukan dekorasi berlebihan — durasi singkat (150–250ms), hormati `prefers-reduced-motion`.
- Fokus keyboard tetap terlihat jelas (outline emas `--brass-gold` tebal 2px) di atas komponen bertekstur.
- Skala maksimalis tekstur dipakai secukupnya: satu elemen "signature" (gauge analog health score) jadi pusat perhatian, elemen lain (kartu, tombol) tekstur lebih halus/tenang agar tidak ramai.

## 11. Roadmap Implementasi Bertahap

**Fase 1 — MVP (mendesak)**
- Struktur Task di bawah Fase (PIC, start/end, bobot)
- Formula progress otomatis bottom-up (Task → Fase → Proyek)
- WYSIWYG untuk deskripsi task & jurnal
- Lampiran file di task

**Fase 2**
- Subtask/Checklist
- Dependency antar-task
- Health score formula diperkuat (4 faktor)
- Notifikasi deadline & bobot tidak seimbang

**Fase 3**
- Laporan mingguan otomatis (PDF/Excel)
- Insight otomatis yang lebih spesifik per faktor
- Role & permission granular
- Analitik beban kerja per PIC

---

## 12. Ringkasan

Inti perubahan dari sistem existing ke sistem yang diusulkan:

- **Dari**: progress proyek/fase diinput manual per level.
- **Menjadi**: progress dihitung otomatis bottom-up dari task nyata, berbobot sesuai kepentingan pekerjaan, sehingga angka progress benar-benar merepresentasikan kondisi riil proyek dan bisa dipertanggungjawabkan ke stakeholder.
