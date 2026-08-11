<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'bobot_pct' => 'decimal:2',
        'progress_pct' => 'decimal:2',
        'estimasi_effort_jam' => 'decimal:2',
    ];

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function pics()
    {
        return $this->belongsToMany(Pic::class, 'task_pics')->withPivot('peran');
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')->where('attachable_type', 'task');
    }

    public function journals()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
