<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answerlog extends Model
{
    use HasFactory;

    protected $table = 'answerlogs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'question_id',
        'testlog_id',
        'mark',
    ];
    
    public function question()
    {
        return $this->hasOne(Question::class, 'id', 'question_id');
    } 

    public function testlog()
    {
        return $this->hasOne(Testlog::class);
    } 

    public function answers()
    {
        return $this->belongsToMany(Answer::class, 'answers_answerlogs');
    }
}
