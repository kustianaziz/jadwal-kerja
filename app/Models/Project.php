<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'tanggal_mulai' => 'date',
        'target_selesai' => 'date',
        'completed_at' => 'datetime',
        'progress_pct' => 'decimal:2',
        'bobot_pct' => 'decimal:2',
        'is_bobot_seimbang' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ProjectGroup::class, 'group_id');
    }

    public function pm()
    {
        return $this->belongsTo(User::class, 'pm_user_id');
    }

    public function pics()
    {
        return $this->belongsToMany(Pic::class, 'project_pics', 'project_id', 'pic_id')->withPivot('peran');
    }

    public function phases()
    {
        return $this->hasMany(Phase::class)->orderBy('urutan');
    }

    public function journals()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'entity_id')->where('entity_type', 'project');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')->where('attachable_type', 'project');
    }
}
