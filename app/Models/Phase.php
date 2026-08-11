<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_target' => 'date',
        'completed_at' => 'datetime',
        'bobot_pct' => 'decimal:2',
        'progress_pct' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->whereNull('parent_task_id');
    }

    public function journals()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
