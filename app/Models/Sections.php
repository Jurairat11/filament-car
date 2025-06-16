<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    use HasFactory;
    protected $primaryKey = 'sec_id';

    protected $fillable = [
        'sec_name',
        'dept_id'
    ];

    public function department() {
        return $this->belongsTo(Department::class,'dept_id','dept_id');
    }

    public function section() {
        return $this->hasMany(Sections::class,'sec_id','sec_id');
    }
}


