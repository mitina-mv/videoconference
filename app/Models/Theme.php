<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
