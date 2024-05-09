<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'is_private',
        'text',
        'settings',
        'user_id',
        'theme_id',
        'type', // 'single', 'multiple', 'text'
        'mark'
    ];
    public $timestamps = false;
    protected $table = 'questions';
    protected $primaryKey = 'id';
        
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    } 

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function correct_answers()
    {
        return $this->hasMany(Answer::class, 'question_id')->where('status', true);
    }
}
