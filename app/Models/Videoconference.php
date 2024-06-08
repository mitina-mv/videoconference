<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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

    protected $casts = [
        'created_at' => 'datetime',
        'settings' => AsArrayObject::class,
        'date' => 'datetime',
    ];

    protected $appends = ['is_completed', 'is_active'];

    public function getIsCompletedAttribute()
    {
        $now = Carbon::now()->timezone('Europe/Moscow')->addMinutes(6);
        $date = Carbon::parse($this->date, 3);
        return $date && $date->lt($now);
    }

    public function getIsActiveAttribute()
    {
        $now = Carbon::now()->timezone('Europe/Moscow');
        $date = Carbon::parse($this->date, 3);
        $start = $date->copy()->subMinutes(5);
        $end = $date->copy()->addMinutes(30);

        return $now->between($start, $end);
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->timezone('Europe/Moscow')->format('d.m.Y H:i');
    }

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
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
