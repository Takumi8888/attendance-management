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
		$work_day = Carbon::now()->format('Y-m-d');
		$year = intval(substr($work_day, 0, 4));
		$month = intval(substr($work_day, 5, 2));
		// 前月
		if ($month == 1) {
			$year -= 1;
			$prevMonth = 12;
		} else {
			$prevMonth = $month - 1;
		}

		// 翌月
		if ($month == 12) {
			$year += 1;
			$nextMonth = 1;
		} else {
			$nextMonth = $month + 1;
		}

		for ($month = $prevMonth; $month <= $nextMonth; $month++) {
			for ($day = 1; $day <= 31; $day++) {
				for ($user = 1; $user <= 6; $user++) {
					if (($month == 1 || $month == 3 || $month == 5 && $day <= 31) || ($month == 4 && $day <= 30) || ($month == 2 && $day <= 28)) {
						if ($day % 5 == 0) {
							$clock_in_time = $year . '/' . $month . '/' . $day . ' ' . rand(9, 10) . ':00:00';
						} else {
							$clock_in_time =  $year . '/' . $month . '/' . $day . ' 9:00:00';
						}
						$clock_out_time =  $year . '/' . $month . '/' . $day . ' 18:00:00';
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
