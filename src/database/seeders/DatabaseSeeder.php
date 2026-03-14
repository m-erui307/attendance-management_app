<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AttendanceSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    public function run(): void
    {
        $this->call([
            AdminsTableSeeder::class,
            UserSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
