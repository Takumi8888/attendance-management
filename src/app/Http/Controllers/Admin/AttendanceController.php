<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AttendanceController extends Controller
{
	public function index()
	{
		$workTimes = WorkTime::whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m-d') . '%')->get();
		$work_day = Carbon::now()->format('Y/m/d');
		$attendances = Attendance::all();

		return view('Admin.work_day_list', compact('workTimes', 'work_day', 'attendances'));
	}

	public function paginationDate(Request $request)
	{
		$year = intval(substr($request->work_day, 0, 4));
		$month = intval(substr($request->work_day, 5, 2));
		$date = intval(substr($request->work_day, 8, 2));

		// 前日ボタン押下
		if ($request->has('prevDate')) {
			if ($month == 1 && $date == 1) {
				$year -= 1;
				$month = 12;
				$date = 31;
			} elseif (($month == 2 || $month == 4 || $month == 6 || $month == 8 || $month == 9 || $month == 11) && $date == 1) {
				$month -= 1;
				$date = 31;
			} elseif (($month == 5 || $month == 7 || $month == 10 || $month == 12) && $date == 1) {
				$month -= 1;
				$date = 30;
			} elseif ($month == 3 && $date == 1 && ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0))) {
				$month -= 1;
				$date = 29;
			} elseif ($month == 3 && $date == 1){
				$month -= 1;
				$date = 28;
			} else {
				$date -= 1;
			}
			$work_day = Carbon::createFromDate($year, $month, $date);
		}

		// 翌日ボタン押下
		if ($request->has('nextDate')) {
			if ($month == 12 && $date == 31) {
				$year += 1;
				$month = 1;
				$date = 1;
			} elseif ((($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10) && $date == 31)
			|| (($month == 4 || $month == 6 || $month == 9 || $month == 11) && $date == 30)) {
				$month += 1;
				$date = 1;
			} elseif ($month == 2 && ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0)) && $date == 28) {
				$date = 29;
			} elseif($month == 2 && ($date == 28 || $date == 29)) {
				$month += 1;
				$date = 1;
			} else {
				$date += 1;
			}
			$work_day = Carbon::createFromDate($year, $month, $date);
		}

		$workTimes = WorkTime::whereDate('clock_in_time', $work_day->format('Y-m-d'))->get();
		$work_day = $work_day->format('Y/m/d');
		$attendances = Attendance::all();

		return view('Admin.work_day_list', compact('workTimes', 'work_day', 'attendances'));
	}


// スタッフ関係
	public function staff()
	{
		$users = User::all();
		return view('Admin.staff_list', compact('users'));
	}

	public function staffAttendance(User $user)
	{
		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m') . '%')->get();
		$work_month = Carbon::now()->format('Y/m');
		$first_day = Carbon::now()->startOfMonth();
		$last_day = Carbon::now()->endOfMonth()->format('d');
		$attendances = Attendance::all();

		return view('Common.attendance_list', compact('user', 'workTimes', 'work_month', 'first_day', 'last_day', 'attendances'));
	}

	public function paginationMonth(Request $request, User $user)
	{
		$year = intval(substr($request->work_month, 0, 4));
		$month = intval(substr($request->work_month, 5, 2));
		$date = 1;

		// 前月ボタン押下
		if ($request->has('prevMonth')) {
			if ($month == 1) {
				$year -= 1;
				$month = 12;
			} else {
				$month -= 1;
			}
			$first_day = Carbon::createFromDate($year, $month, $date);
		}

		// 翌月ボタン押下
		if ($request->has('nextMonth')) {
			if ($month == 12) {
				$year += 1;
				$month = 1;
			} else {
				$month += 1;
			}
			$first_day = Carbon::createFromDate($year, $month, $date);
		}

		// 月末日
		if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) {
			$last_day = 31;
		} elseif ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
			$last_day = 30;
		} elseif ($month == 2 && ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0))) {
			$last_day = 29;
		} elseif ($month == 2) {
			$last_day = 28;
		}

		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $first_day->format('Y-m') . '%')->get();
		$work_month = $first_day->format('Y/m');
		$attendances = Attendance::all();

		return view('Common.attendance_list', compact('user', 'workTimes', 'work_month', 'first_day', 'last_day', 'attendances'));
	}

	public function export(Request $request, User $user)
	{
		$csvHeader = [
			'名前',
			'出勤ID',
			'日付',
			'出勤時間',
			'退勤時間',
			'休憩時間',
			'実労働時間',
		];

		$temps = [];
		array_push($temps, $csvHeader);

		$month = substr($request->work_month, 0, 4) . "-" . substr($request->work_month, 5, 2);
		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $month . '%')->get();

		foreach ($workTimes as $workTime) {
			$temp = [
				$user->name,
				$workTime->id,
				$work_day = Attendance::where('work_time_id', $workTime->id)->first()->work_day,
				substr($workTime->clock_in_time, 11, 8),
				substr($workTime->clock_out_time, 11, 8),
				Attendance::where('work_time_id', $workTime->id)->first()->total_break_time,
				Attendance::where('work_time_id', $workTime->id)->first()->actual_working_hours,
			];
			array_push($temps, $temp);
		}

		$stream = fopen('php://temp', 'r+b');
		foreach ($temps as $temp) {
			fputcsv($stream, $temp);
		}
		rewind($stream);
		$csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
		$csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
		$filename = substr($work_day, 0, 4) . "年" . substr($work_day, 5, 2) . "月分勤怠一覧：" . $user->name . ".csv";
		$headers = array(
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
		);
		return Response::make($csv, 200, $headers);
	}
}
