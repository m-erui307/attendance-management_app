<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'target_date',
    'clock_in',
    'clock_out',
    'breaks',
    'remark',
    'status',
    ];

    protected $casts = [
    'breaks' => 'array',
    'target_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
