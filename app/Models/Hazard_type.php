<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hazard_type extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'type_name',
        'type_desc'
    ];

}
