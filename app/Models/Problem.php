<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

    class Problem extends Model
    {
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable = [
        'prob_id',
        'prob_desc',
        'prob_date',
        'prob_img',
        'status',
        'dismiss_reason',
        'user_id',
        'dept_id',
        'title',
        'place',
        'prob_img_path'
    ];

    // public static function generateNextProbId(): string
    // {
    //     $year = now()->format('y'); // เช่น '25'
    //     $prefix = 'P-';

    //     $last = self::where('prob_id', 'like', "P-%/{$year}")
    //         ->orderByDesc('id')
    //         ->first();

    //     $lastNumber = 0;

    //     if ($last && preg_match('/P-(\d{3})\/' . $year . '/', $last->prob_id, $matches)) {
    //         $lastNumber = (int) $matches[1];
    //     }

    //     $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

    //     return "P-{$nextNumber}/{$year}";
    // }

    public static function generateProbId(): string
{
    return DB::transaction(function () {
        $year = now()->format('y');
        $prefix = 'P-';

        $latest = self::whereYear('created_at', now()->year)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $lastRunning = 0;

        if ($latest && preg_match('/P-(\d+)\/' . $year . '/', $latest->prob_id, $matches)) {
            $lastRunning = (int) $matches[1];
        }

        $nextRunning = str_pad($lastRunning + 1, 3, '0', STR_PAD_LEFT);
        return "{$prefix}{$nextRunning}/{$year}";
    });
}

    public function setImgBeforePathAttribute($value)
    {
        // เซ็ต path และเซ็ต URL เต็มให้ prob_img ด้วย
        $this->attributes['prob_img_path'] = $value;
        //$this->attributes['prob_img'] = url('storage/' . $value);
    }

    public function department() {
        return $this->belongsTo(Department::class,'dept_id','dept_id');
    }

    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
