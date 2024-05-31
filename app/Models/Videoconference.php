<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Videoconference extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'videoconferences';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'date',
        'settings',
        'name',
        'session', // uuid
    ];

    // relationships
    public function studgroups()
    {
        return $this->belongsToMany(Studgroup::class, 'videoconference_studgroup', 'vc_id', 'studgroup_id');
    }

    public function assignment()
    {
        return $this->hasOne(Assignment::class, 'vc_id');
    }

    public function files()
    {
        return $this->belongsToMany(File::class);
    }
}
