<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];
    protected $table = 'disciplines';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
