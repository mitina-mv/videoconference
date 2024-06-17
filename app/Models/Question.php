<?php

namespace App\Models;

use App\Http\Service\ComplexityCalculatorService;
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

    protected $appends = ['correct_answers', 'complexity_percent'];

    public function getCorrectAnswersAttribute()
    {
        return $this->answers()->where('status', true)->get();
    }

    public function getComplexityPercentAttribute()
    {
        $calculator = new ComplexityCalculatorService();
        $p = null;
        try {
            $p = (100 - $calculator->calculateQuestionComplexity($this->id)) . '%';
        } catch (\Exception $e) {
            $p = '-';
        }
        return $p;
    }
        
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
