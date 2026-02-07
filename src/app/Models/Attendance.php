<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
    'clock_in'  => 'datetime',
    'clock_out' => 'datetime',
    'work_date' => 'date',
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
