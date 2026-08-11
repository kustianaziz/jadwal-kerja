<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
