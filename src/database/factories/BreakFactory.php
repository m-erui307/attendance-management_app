<?php

namespace Database\Factories;

use App\Models\BreakTimeModel;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = BreakModel::class;

    public function definition()
    {
        $attendance = Attendance::inRandomOrder()->first();

        $breakStart = (clone $attendance->clock_in)->modify('+'.rand(2,4).' hours');
        $breakEnd = (clone $breakStart)->modify('+'.rand(30,60).' minutes');

        return [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}
