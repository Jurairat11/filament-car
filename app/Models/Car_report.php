<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'responsible_dept_id',
        'close_car_date',
        'equipment',
        'place_id',
        'hazard_source_id',
        'car_delay',
        'reopen_car_reason',
        'img_before_path'
    ];

    // public static function generateNextCarNo(): string
    // {
    //     $year = now()->format('y'); // ปี พ.ศ. สั้น เช่น 25

    //     $prefix = "C-";

    //     // นับรายการในปีปัจจุบัน
    //     $count = self::whereYear('created_at', now()->year)->count() + 1;

    //     $runningNumber = str_pad($count, 3, '0', STR_PAD_LEFT);

    //     return "{$prefix}{$runningNumber}/{$year}";
    // }

    public static function generateCarNo(): string
    {
        return DB::transaction(function () {
            $year = now()->format('y'); // เช่น '25'
            $prefix = 'C-';

            // หารายการล่าสุดของปีนี้ แล้ว lock row เดียว
            $latest = self::whereYear('created_at', now()->year)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $lastRunning = 0;

            if ($latest && preg_match('/C-(\d+)\/' . $year . '/', $latest->car_no, $matches)) {
                $lastRunning = (int) $matches[1];
            }

            $nextRunning = str_pad($lastRunning + 1, 3, '0', STR_PAD_LEFT);
            return "{$prefix}{$nextRunning}/{$year}";
        });
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
        return $this->belongsTo(Sections::class, 'sec_id','sec_id');
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
        return $this->belongsTo(Car_report::class, 'parent_car_id','id');
    }

    public function followUp()
    {
        return $this->belongsTo(Car_report::class, 'followed_car_id','id');
    }
    // public function children()
    // {
    //     return $this->hasOne(Car_report::class, 'followed_car_id');
    // }

    public function hazardSource() {
        return $this->belongsTo(Hazard_source::class, 'hazard_source_id','id');
    }
    public function place() {
        return $this->belongsTo(Place::class, 'place_id','id');
    }

}
