<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_done' => 'boolean',
        'bobot_pct' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
