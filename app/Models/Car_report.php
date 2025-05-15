<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car_report extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable = [
        'car_no',
        'car_date',
        'car_due_date',
        'car_desc',
        'img_before',
        'status',
        'dept_id',
        'sec_id',
        'hazard_level_id',
        'hazard_type_id',
        'problem_id',
        'created_by',
        'parent_car_id',
        'followed_car_id',
        'responsible_dept_id'
    ];

    public static function generateNextCarNo(): string
    {
        $year = now()->format('y'); // ปี พ.ศ. สั้น เช่น 25
        $prefix = "C-";

        // นับรายการในปีปัจจุบัน
        $count = self::whereYear('created_at', now()->year)->count() + 1;

        $runningNumber = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$runningNumber}/{$year}";
    }

    public function mount(): void
    {
        parent::mount();

        $problemId = request()->get('id');

        if ($problemId) {
            $this->form->fill([
                'prob_id' => $problemId,
                'status' => 'accepted',
            ]);
        }
    }

    public function department() {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function section() {
        return $this->belongsTo(Section::class, 'sec_id','sec_id');
    }

    public function hazardLevel() {
        return $this->belongsTo(Hazard_level::class, 'hazard_level_id','id');
    }

    public function hazardType() {
        return $this->belongsTo(Hazard_type::class, 'hazard_type_id','id');
    }

    public function problem() {
        return $this->belongsTo(Problem::class, 'problem_id','id');
    }
    public function users() {
        return $this->belongsTo(User::class, 'created_by','id');
    }

    public function responsible() {
        return $this->belongsTo(Department::class, 'responsible_dept_id');
    }

    public function carResponse() {
        return $this->hasOne(Car_responses::class, 'car_id','id');
    }

    public function parent()
    {
        return $this->belongsTo(Car_report::class, 'parent_car_id');
    }
    public function followUp()
    {
        return $this->belongsTo(Car_report::class, 'follow_car_id');
    }
    public function children()
    {
        return $this->hasOne(Car_report::class, 'followed_car_id');
    }

}
