<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discipline_id',
    ];
    protected $table = 'themes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    protected $appends = ['question_count'];

    public function getQuestionCountAttribute()
    {
        $user = Auth::user();
        
        return $this->questions()
            ->where(function($query) use ($user) {
                $query->where('is_private', false)
                      ->orWhere(function($query) use ($user) {
                          $query->where('is_private', true)
                                ->where('user_id', $user->id);
                      });
            })
            ->whereHas('correct_answers')
            ->count();
    }
}
