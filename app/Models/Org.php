<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Org extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'info'
    ];

    public $timestamps = false;
    protected $table = 'orgs';
    protected $primaryKey = 'id';
}
