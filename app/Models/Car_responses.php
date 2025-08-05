<?php

namespace App\Models;

use Carbon\Carbon;
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
        'status_reply',
        'temp_responsible',
        'perm_responsible',
        'img_after_path',
        'actual_date',
        'days_perm_value',

    ];

    // public function getDaysPermAttribute()
    // {

    //     if ($this->perm_status === 'finished') {
    //         return null;
    //     }

    //     return round( now()->diffInDays($this->perm_due_date, false));
    // }

    public function getDaysPermAttribute()
    {
    // หยุดการคำนวณ ถ้าสถานะคือ finished หรือ actual_date ตรงกับวันนี้
    if ($this->perm_status === 'finished' || ($this->actual_date && now()->eq(Carbon::parse($this->actual_date)))) {
        return null;
    }

    $days = round(now()->diffInDays($this->perm_due_date, false)) + 1;

    if ($days === -0.0) {
        $days = 0;
    }

    return $days + ($this->days_perm_value ?? 0);
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
