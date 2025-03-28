<?php

namespace Database\Seeders;

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
		$note = ['電車遅延のため', '体調不良のため', '私用のため'];

		for ($i = 1; $i <= 906; $i++) {
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