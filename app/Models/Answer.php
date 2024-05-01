<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'question_id',
    ];
    public $timestamps = false;

    public function answerlogs()
    {
        return $this->belongsToMany(Answerlog::class, 'answers_answerlogs');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    } 
}
