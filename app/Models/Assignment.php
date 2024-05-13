<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'date',
    ];
    protected $table = 'assignments';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
        'date' => 'datetime',
    ];

    
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    } 

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
