<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    protected $casts = [
    'break_start' => 'datetime',
    'break_end'   => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getBreakMinutesAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return $this->break_start
            ->diffInMinutes($this->break_end);
    }

    public function getBreakTimeAttribute()
    {
        $minutes = $this->break_minutes;

        return sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
    }
}
