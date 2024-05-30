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
        'mark',
        'time',
        'user_id',
        'assignment_id',
        'uncorrect_answers',
    ];

    protected $casts = [
        'uncorrect_answers' => 'array',
        'created_at' => 'datetime',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function answerlogs()
    {
        return $this->hasMany(Answerlog::class, 'testlog_id', 'id');
    }
}
