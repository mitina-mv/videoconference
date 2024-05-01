<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'description',
        'settings',
        'name',
        'user_id',
        'discipline_id'
    ];
    public $timestamps = true;
    protected $table = 'tests';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function discipline()
    {
        return $this->hasOne(Discipline::class);
    }

    public function questionCount()
    {
        return isset($this->test_settings) ? json_decode($this->test_settings)->question_count : 0;
    }
}
