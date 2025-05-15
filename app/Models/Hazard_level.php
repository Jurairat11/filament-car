<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hazard_level extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'level_name',
        'level_desc'

    ];

}
