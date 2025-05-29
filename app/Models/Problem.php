<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'dept_id'
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

    public function department() {
        return $this->belongsTo(Department::class,'dept_id','dept_id');
    }

    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
