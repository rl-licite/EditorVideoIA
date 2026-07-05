<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRecord extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'status',
        'size',
        'notes',
    ];

    public function getSizeFormattedAttribute(): string
    {
        return number_format(($this->size ?? 0) / 1024 / 1024, 2, ',', '.') . ' MB';
    }
}
