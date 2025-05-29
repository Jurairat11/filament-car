<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hazard_source extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_name',
    ];
}
