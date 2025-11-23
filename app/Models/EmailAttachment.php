<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    public function getHumanFileSizeAttribute()
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 2) . ' KB';
        } else {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
    }
}

