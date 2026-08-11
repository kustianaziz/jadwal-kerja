<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
