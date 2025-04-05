<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use App\Models\User;
use App\Models\WorkTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		// 3か月の合計日数
		$work_day = Carbon::now()->format('Y-m-d');
		$month = intval(substr($work_day, 5, 2));
		if ($month == 4 || $month == 6 || $month == 7 || $month == 8 || $month == 9 || $month == 11 || $month == 12) {
			$total_work_day = 92;
		} elseif ($month == 5 || $month == 10) {
			$total_work_day = 91;
		} elseif ($month == 1 || $month == 2) {
			$total_work_day = 90;
		} elseif ($month == 3) {
			$total_work_day = 89;
		}

		$user_count = count(User::all());
		$total_count = $total_work_day * $user_count;

		for ($i = 1; $i <= $total_count; $i++) {
			$work_time = WorkTime::find($i)->work_time;
			$date = WorkTime::find($i)->clock_in_time;
			$total_break_time = BreakTime::find($i)->break_time;
			$actual_working_hours = ((strtotime($work_time) - strtotime($total_break_time)) / 3600) . ':00:00';

			DB::table('attendances')->insert([
				'work_time_id'         => $i,
				'work_day'             => $date,
				'total_break_time' 	   => $total_break_time,
				'actual_working_hours' => $actual_working_hours,
				'created_at'           => Carbon::now(),
				'updated_at'           => Carbon::now(),
			]);
		}
    }
}
