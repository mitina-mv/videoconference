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
    ];

    // relationships
    public function studgroups()
    {
        return $this->belongsToMany(Studgroup::class, 'videoconference_studgroup');
    }

    public function assignment()
    {
        return $this->hasOne(Assignment::class);
    }
}
