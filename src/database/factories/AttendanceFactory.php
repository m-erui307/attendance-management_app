<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');

        $clockIn = (clone $date)->setTime(rand(8,10), rand(0,59), 0);
        $clockOut = (clone $clockIn)->modify('+'.rand(7,9).' hours');

        return [
            'user_id' => User::factory(),
            'work_date' => $clockIn->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'remark' => $this->faker->optional()->sentence(),
        ];
    }
}
