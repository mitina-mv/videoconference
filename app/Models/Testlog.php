<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testlog extends Model
{
    use HasFactory;

    protected $table = 'testlogs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'date',
        'mark',
        'time',
        'user_id',
        'test_id',
        'teacher_id',
        'uncorrect_answers',
    ];

    protected $casts = [
        'uncorrect_answers' => 'array',
        'created_at' => 'datetime',
        'date' => 'datetime',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'id');
    }

    public function answerlogs()
    {
        return $this->hasMany(Answerlog::class, 'testlog_id', 'id');
    }
}
