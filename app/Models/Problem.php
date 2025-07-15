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

    public static function generateNextProbId(): string
    {
        $year = now()->format('y'); // เช่น '25'
        $prefix = 'P-';

        $last = self::where('prob_id', 'like', "P-%/{$year}")
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($last && preg_match('/P-(\d{3})\/' . $year . '/', $last->prob_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "P-{$nextNumber}/{$year}";
    }

    public static function createProblemWithRetry(int $maxRetries = 5): ?Problem
    {
        $attempts = 0;

        while ($attempts < $maxRetries) {
            try {
                return DB::transaction(function () {
                    DB::table('problems')->sharedLock()->get();

                    $probId = self::generateNextProbId();

                    return Problem::create(['prob_id' => $probId]);
                });
            } catch (QueryException $e) {
                if ($e->getCode() === '23505') { // duplicate key
                    $attempts++;
                    usleep(100000); // หน่วงเวลา 100ms
                    continue;
                }

                throw $e;
            }
        }

        return null;
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
