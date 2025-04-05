<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CorrectionRequestsTableSeeder extends Seeder
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

		$note = ['電車遅延のため', '体調不良のため', '私用のため'];

		// 単体テスト（ApprovalTest）確認用としてid=1を設定
		DB::table('correction_requests')->insert([
			'attendance_id'    => 1,
			'user_id'          => 1,
			'admin_id'         => null,
			'application_date' => Carbon::now(),
			'status'           => 1,
			'note'             => '電車遅延のため',
			'created_at'       => Carbon::now(),
			'updated_at'       => Carbon::now(),
		]);

		for ($i = 2; $i <= $total_count; $i++) {
			$clock_in_time = WorkTime::find($i)->clock_in_time;

			if (strpos($clock_in_time, '10:00:00') !== false) {
				$user_id = WorkTime::find($i)->user_id;
				$status = rand(1, 2);

				if ($status == 1) {
					DB::table('correction_requests')->insert([
						'attendance_id'    => $i,
						'user_id'          => $user_id,
						'admin_id'         => null,
						'application_date' => Carbon::now(),
						'status'           => $status,
						'note'             => $note[rand(0, 2)],
						'created_at'       => Carbon::now(),
						'updated_at'       => Carbon::now(),
					]);

				} else {
					DB::table('correction_requests')->insert([
						'attendance_id'    => $i,
						'user_id'          => $user_id,
						'admin_id'         => 1,
						'application_date' => Carbon::now(),
						'status'           => $status,
						'note'             => $note[rand(0, 2)],
						'created_at'       => Carbon::now(),
						'updated_at'       => Carbon::now(),
					]);
				}
			}
		}
    }
}