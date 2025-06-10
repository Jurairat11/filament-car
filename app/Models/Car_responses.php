<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car_responses extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable =[
        'car_id',
        'cause',
        'img_after',
        'temp_desc',
        'temp_due_date',
        'temp_responsible_id',
        'temp_status',
        'perm_desc',
        'perm_due_date',
        'perm_responsible_id',
        'perm_status',
        'preventive',
        'status',
        'created_by',
        'status_reply'

    ];

    public function getDaysPermAttribute()
    {
        if (!$this->perm_desc || $this->perm_status === 'finished') {
            return null;
        }

        $value = round( now()->diffInDays($this->perm_due_date, false));
        return $value === 0 ? 0 : $value;


    }

    public function carReport() {
        return $this->belongsTo(Car_report::class,'car_id','id');
    }

    public function tempResponsible(){
        return $this->belongsTo(User::class,'temp_responsible_id','id');
    }
    public function permResponsible(){
        return $this->belongsTo(User::class,'perm_responsible_id','id');
    }

    public function createdResponse(){
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function department() {
        return $this->belongsTo(Department::class,'dept_id','dept_id');
    }
}
