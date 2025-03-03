<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'messages',
        'metrics',
        'path',
        'is_completed',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'settings' => AsArrayObject::class,
        'messages' => AsArrayObject::class,
        'metrics' => AsArrayObject::class,
        'date' => 'datetime',
        'path' => 'string'
    ];

    protected $appends = ['is_old', 'is_active', 'path_full'];  
    // protected $appends = ['is_old', 'is_active'];

    public function getIsOldAttribute()
    {
        $now = Carbon::now()->timezone('Europe/Moscow')->addMinutes(5);
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

    public function getPathFullAttribute()
    {
        return $this->path ? asset(Storage::url($this->path)) : null;
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
        return $this->hasMany(File::class, 'videoconference_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
