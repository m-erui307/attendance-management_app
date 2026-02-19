<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function getBreakMinutesAttribute()
{
    return $this->breaks->sum(function ($break) {
        if (!$break->break_end) {
            return 0;
        }

        return $break->break_start
            ->diffInMinutes($break->break_end);
    });
}

public function getBreakTimeAttribute()
{
    $minutes = $this->break_minutes;

    return sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
}

public function getWorkMinutesAttribute()
{
    if (!$this->clock_in || !$this->clock_out) {
        return 0;
    }

    return $this->clock_in
        ->diffInMinutes($this->clock_out)
        - $this->break_minutes;
}

public function getTotalTimeAttribute()
{
    $minutes = $this->work_minutes;

    return sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
}
}
