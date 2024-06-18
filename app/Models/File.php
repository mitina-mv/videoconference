<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;
    protected $table = 'files';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'path',
        'videoconference_id',
        'name',
    ];

    protected $appends = ['path_full']; 

    public function getPathFullAttribute()
    {
        return $this->path ? asset(Storage::url($this->path)) : null;
    }
}
