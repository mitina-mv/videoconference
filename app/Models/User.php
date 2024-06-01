<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $table = 'users';
    public $timestamps = true;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lastname',
        'patronymic',
        'role_id',
        'studgroup_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Виртуальный атрибут для главной фотографии объявления
    protected $appends = ['full_name', 'initials'];

    public function getFullNameAttribute()
    {
        return $this->lastname . " " . $this->name 
        . ($this->patronymic ? ' ' . $this->patronymic : '');
    }

    public function getInitialsAttribute()
    {
        $lastname = $this->lastname;
        $name = $this->name;

        // Получаем первые буквы имени и фамилии (если они существуют)
        $initials = '';
        if (!empty($lastname)) {
            $initials .= mb_substr($lastname, 0, 1);
        }
        if (!empty($name)) {
            $initials .= mb_substr($name, 0, 1);
        }

        return $initials;
    }

    // relationships
    public function studgroups()
    {
        return $this->belongsToMany(Studgroup::class, 'users_studgroups');
    }

    public function studgroup()
    {
        return $this->belongsTo(Studgroup::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}
