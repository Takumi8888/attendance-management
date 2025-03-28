<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		for ($month = 1; $month <= 5; $month++) {
			for ($day = 1; $day <= 31; $day++) {
				for ($user = 1; $user <= 6; $user++) {
					if (($month == 1 || $month == 3 || $month == 5 && $day <= 31) || ($month == 4 && $day <= 30) || ($month == 2 && $day <= 28)) {
						if ($day % 5 == 0) {
							$clock_in_time = '2025/' . $month . '/' . $day . ' ' . rand(9, 10) . ':00:00';
						} else {
							$clock_in_time = '2025/' . $month . '/' . $day . ' 9:00:00';
						}
						$clock_out_time = '2025/' . $month . '/' . $day . ' 18:00:00';
						$work_time = ((strtotime($clock_out_time) - strtotime($clock_in_time)) / 3600) . ':00:00';

						DB::table('work_times')->insert([
							'user_id' 		 => $user,
							'clock_in_time'  => $clock_in_time,
							'clock_out_time' => $clock_out_time,
							'work_time'		 => $work_time,
							'created_at' 	 => Carbon::now(),
							'updated_at' 	 => Carbon::now(),
						]);
					}
				}
			}
		}
    }
}
