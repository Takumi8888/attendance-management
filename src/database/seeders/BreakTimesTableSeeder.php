<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BreakTimesTableSeeder extends Seeder
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

		$work_time_id = 1;
		for ($month = $prevMonth; $month <= $nextMonth; $month++) {
			for ($day = 1; $day <= 31; $day++) {
				for ($user = 1; $user <= 6; $user++) {
					if ((($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) && $day <= 31) ||
						(($month == 4 || $month == 6 || $month == 9 || $month == 11) && $day <= 30) || ($month == 2 && $day <= 28)) {
						$start_time  = $year . '/' . $month . '/' . $day . ' 12:00:00';
						$end_time = $year . '/' . $month . '/' . $day . ' 13:00:00';
						$break_time = ((strtotime($end_time) - strtotime($start_time)) / 3600) . ':00:00';

						DB::table('break_times')->insert([
							'work_time_id' => $work_time_id,
							'start_time'   => $start_time,
							'end_time' 	   => $end_time,
							'break_time'   => $break_time,
							'created_at'   => Carbon::now(),
							'updated_at'   => Carbon::now(),
						]);

						$work_time_id += 1;
					}
				}
			}
		}
    }
}
