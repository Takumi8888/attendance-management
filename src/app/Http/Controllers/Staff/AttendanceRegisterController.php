<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\WorkTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRegisterController extends Controller
{
	// 出勤（status == 0）
	public function index()
	{
		$user = Auth::id();
		$date = Carbon::now()->format('Y-m-d');

		// 判定要素
		$check_clockIn = WorkTime::where('user_id', $user)->whereDate('clock_in_time', $date)->first();
		$check_clockOut = WorkTime::where('user_id', $user)->whereDate('clock_out_time', $date)->first();
		$workTime = WorkTime::where('user_id', $user)->whereDate('clock_in_time', $date)->first();

		// 表示画面
		if (empty($check_clockIn)) {
			// 表示画面：勤務外
			$status = 0;
			return view('Staff.attendance_register', compact('status', 'user'));

		} elseif (isset($check_clockIn) && empty($check_clockOut)) {
			$breakTimeAll = BreakTime::where('work_time_id', $workTime->id)->get();
			if (count($breakTimeAll) >= 1) {
				for ($i = 0; $i < count($breakTimeAll); $i++) {
					$check_breakTimeStart = BreakTime::find($breakTimeAll[$i]->id)->start_time;
					$check_breakTimeEnd = BreakTime::find($breakTimeAll[$i]->id)->end_time;
					if (isset($check_breakTimeStart) && empty($check_breakTimeEnd)) {
						// 表示画面：休憩中
						$status = 2;
						$breakTime = BreakTime::find($breakTimeAll[$i]->id);
						return view('Staff.attendance_register', compact('status', 'user', 'breakTime'));
					}
				}
			}

			/// 表示画面：出勤中
			$status = 1;
			return view('Staff.attendance_register', compact('status', 'user', 'workTime'));

		} elseif (isset($check_clockOut)) {
			// 表示画面：退勤済
			$status = 3;
			return view('Staff.attendance_register', compact('status', 'user'));
		}
	}

	// 出勤ボタン押下 → 退勤・休憩入ボタン表示（status == 1）
	public function clockIn()
	{
		$status = 1;
		$user = Auth::id();
		$date = Carbon::now()->format('Y-m-d');

		// 出勤時間
		$clockIn = Carbon::now()->format('Y-m-d H:i:s');

		$workTime = WorkTime::create([
			'user_id'       => $user,
			'clock_in_time' => $clockIn,
		]);

		$attendance = Attendance::create([
			'work_time_id' => $workTime->id,
			'work_day'     => $date,
		]);

		return view('Staff.attendance_register', compact('status', 'user', 'workTime'));
	}

	// 休憩入ボタン押下 → 休憩戻ボタン表示（status == 2）
	public function breakTimeStart(Request $request)
	{
		$status = 2;
		$user = Auth::id();

		// 対象データ取得
		$workTime = WorkTime::find($request->id);

		// 休憩入時間
		$breakTimeStart = Carbon::now()->format('Y-m-d H:i:s');

		$breakTime = BreakTime::create([
			'work_time_id' => $workTime->id,
			'start_time'   => $breakTimeStart,
		]);

		return view('Staff.attendance_register', compact('status', 'user', 'workTime', 'breakTime'));
	}

	// 休憩戻ボタン押下 → 退勤・休憩入ボタン表示（status == 1）
	public function breakTimeEnd(Request $request)
	{
		$status = 1;
		$user = Auth::id();

		// 対象データ取得
		$breakTime = BreakTime::find($request->id);

		// 休憩戻時間
		$breakTimeEnd = Carbon::now()->format('Y-m-d H:i:s');

		// 休憩時間
		$startTime = strtotime($breakTime->start_time);
		$endTime = strtotime($breakTimeEnd);
		$timeDifference = $endTime - $startTime;
		$hours = floor($timeDifference / 3600);
		$minutes = floor(($timeDifference % 3600) / 60);
		$seconds = $timeDifference % 60;
		$actual_breakTime = date($hours . ':' . $minutes . ':' . $seconds);

		$breakTime->update([
			'end_time'   => $breakTimeEnd,
			'break_time' => $actual_breakTime,
		]);

		// WorkTimeデータ取得
		$workTime_id = $breakTime->work_time_id;
		$workTime = WorkTime::find($workTime_id);

		// 総休憩時間
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->get();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();
		$count = count($breakTime);
		$today = strtotime(new Carbon('today'));
		$add_breakTime = 0;

		for ($i = 0; $i < $count; $i++) {
			$actual_breakTime = strtotime($breakTime[$i]->break_time);
			$difference_breakTime = $actual_breakTime - $today;
			$add_breakTime = $add_breakTime + $difference_breakTime;
		}

		$hours = floor($add_breakTime / 3600);
		$minutes = floor(($add_breakTime % 3600) / 60);
		$seconds = $add_breakTime % 60;
		$total_break_time = date($hours . ':' . $minutes . ':' . $seconds);

		$attendance->update([
			'total_break_time' => $total_break_time,
		]);

		return view('Staff.attendance_register', compact('status', 'user', 'workTime'));
	}

	// 退勤ボタン押下 → コメント表示（status == 3）
	public function clockOut(Request $request)
	{
		$status = 3;
		$user = Auth::id();

		// 対象データ取得
		$workTime = WorkTime::find($request->id);
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->get();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		// 退勤時間
		$clockOut = Carbon::now()->format('Y-m-d H:i:s');

		// 拘束時間
		$startTime = strtotime($workTime->clock_in_time);
		$endTime = strtotime($clockOut);
		$timeDifference = $endTime - $startTime;
		$hours = floor($timeDifference / 3600);
		$minutes = floor(($timeDifference % 3600) / 60);
		$seconds = $timeDifference % 60;
		$actual_workTime = date($hours . ':' . $minutes . ':' . $seconds);

		// 総休憩時間
		$count = count($breakTime);
		$today = strtotime(new Carbon('today'));
		$add_breakTime = 0;

		for ($i = 0; $i < $count; $i++) {
			$actual_breakTime = strtotime($breakTime[$i]->break_time);
			$difference_breakTime = $actual_breakTime - $today;
			$add_breakTime = $add_breakTime + $difference_breakTime;
		}

		$hours = floor($add_breakTime / 3600);
		$minutes = floor(($add_breakTime % 3600) / 60);
		$seconds = $add_breakTime % 60;
		$total_break_time = date($hours . ':' . $minutes . ':' . $seconds);

		// 実労働時間
		$difference_workTime = strtotime($actual_workTime) - strtotime($total_break_time);
		$hours = floor($difference_workTime / 3600);
		$minutes = floor(($difference_workTime % 3600) / 60);
		$seconds = $difference_workTime % 60;
		$actual_working_hours = date($hours . ':' . $minutes . ':' . $seconds);

		$workTime->update([
			'clock_out_time' => $clockOut,
			'work_time'      => $actual_workTime,
		]);

		$attendance->update([
			'total_break_time'     => $total_break_time,
			'actual_working_hours' => $actual_working_hours,
		]);

		return view('Staff.attendance_register', compact('status', 'user'));
	}
}
