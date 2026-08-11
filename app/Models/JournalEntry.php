<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'project_id',
        'phase_id',
        'task_id',
        'tipe',
        'judul',
        'tanggal',
        'detail',
        'tautan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tautan' => 'array',
        'created_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mentions()
    {
        return $this->belongsToMany(User::class, 'journal_mentions', 'journal_id', 'user_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
