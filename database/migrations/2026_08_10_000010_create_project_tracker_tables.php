<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('email', 150)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('unit_kerja', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('project_groups', function (Blueprint $table) {
            $table->id();
            $table->string('nama_grup', 150);
            $table->text('deskripsi')->nullable();
            $table->date('target_selesai')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('project_groups')->nullOnDelete();
            $table->string('nama_proyek', 150);
            $table->mediumText('deskripsi')->nullable();
            $table->foreignId('pm_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('target_selesai')->nullable();
            $table->enum('prioritas', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['draft', 'berjalan', 'selesai', 'dibatalkan'])->default('berjalan');
            $table->decimal('progress_pct', 5, 2)->default(0.00);
            $table->unsignedTinyInteger('health_score')->default(100);
            $table->enum('health_status', ['healthy', 'attention', 'at_risk', 'critical'])->default('healthy');
            $table->boolean('is_bobot_seimbang')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nama_fase', 150);
            $table->mediumText('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->decimal('bobot_pct', 5, 2)->default(0.00);
            $table->foreignId('pic_id')->nullable()->constrained('pics')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_target')->nullable();
            $table->enum('status', ['belum_mulai', 'in_progress', 'review', 'selesai', 'terlambat'])->default('belum_mulai');
            $table->decimal('progress_pct', 5, 2)->default(0.00);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained('phases')->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->string('nama_task', 200);
            $table->mediumText('deskripsi')->nullable();
            $table->decimal('bobot_pct', 5, 2)->default(0.00);
            $table->date('tanggal_mulai')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('estimasi_effort_jam', 6, 2)->nullable();
            $table->enum('prioritas', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['belum_mulai', 'in_progress', 'review', 'blocked', 'selesai'])->default('belum_mulai');
            $table->decimal('progress_pct', 5, 2)->default(0.00);
            $table->enum('progress_source', ['manual', 'checklist', 'subtask'])->default('manual');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('task_pics', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('pic_id')->constrained('pics')->cascadeOnDelete();
            $table->enum('peran', ['utama', 'kontributor'])->default('kontributor');
            $table->primary(['task_id', 'pic_id']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'depends_on_task_id']);
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('nama_item', 255);
            $table->decimal('bobot_pct', 5, 2)->default(0.00);
            $table->boolean('is_done')->default(false);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->enum('attachable_type', ['project', 'phase', 'task', 'journal']);
            $table->unsignedBigInteger('attachable_id');
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->unsignedBigInteger('ukuran_bytes')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedSmallInteger('versi')->default(1);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->enum('tipe', ['update', 'pencapaian', 'issue', 'system'])->default('update');
            $table->string('judul', 200);
            $table->mediumText('detail')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('journal_mentions', function (Blueprint $table) {
            $table->foreignId('journal_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['journal_id', 'user_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipe', ['deadline', 'bobot_tidak_seimbang', 'health_turun', 'mention', 'blocked']);
            $table->string('judul', 200);
            $table->string('pesan', 500);
            $table->enum('related_type', ['project', 'phase', 'task'])->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['project', 'phase', 'task']);
            $table->unsignedBigInteger('entity_id');
            $table->string('field_changed', 100);
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('journal_mentions');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_pics');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_groups');
        Schema::dropIfExists('pics');
    }
};
