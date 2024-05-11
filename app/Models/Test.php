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
        'theme_id'
    ];
    public $timestamps = true;
    protected $table = 'tests';
    protected $primaryKey = 'id';

    protected $casts = [
        'settings' => 'array'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function questionCount()
    {
        return isset($this->test_settings) ? json_decode($this->test_settings)->question_count : 0;
    }
}
