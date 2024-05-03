<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studgroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
    ];
    public $timestamps = false;
    protected $table = 'studgroups';
    protected $primaryKey = 'id';

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_studgroups');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'studgroup_id', 'id')->orderBy('user_lastname');
    }
}
