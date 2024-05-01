<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    const ROLE_ADMIN = 1;
    const ROLE_TEACHER = 2;
    const ROLE_STUDENT = 3;

    protected $fillable = [
        'name',
    ];
    public $timestamps = false;
    protected $table = 'roles';
    protected $primaryKey = 'id';
}
