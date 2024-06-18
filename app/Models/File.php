<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
