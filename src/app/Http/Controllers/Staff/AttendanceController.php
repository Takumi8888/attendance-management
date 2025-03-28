<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
	public function index()
	{
		$user = Auth::id();
		$workTimes = WorkTime::where('user_id', $user)->whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m') . '%')->get();
		$work_month = Carbon::now()->format('Y/m');
		$first_day = Carbon::now()->startOfMonth();
		$last_day = Carbon::now()->endOfMonth()->format('d');
		$attendances = Attendance::all();

		return view('Common.attendance_list', compact('workTimes', 'work_month', 'first_day', 'last_day', 'attendances'));
	}

	public function paginationMonth(Request $request)
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

		$user = Auth::id();
		$workTimes = WorkTime::where('user_id', $user)->whereDate('clock_in_time', 'like', $first_day->format('Y-m') . '%')->get();
		$work_month = $first_day->format('Y/m');
		$attendances = Attendance::all();

		return view('Common.attendance_list', compact('workTimes', 'work_month', 'first_day', 'last_day', 'attendances'));
	}
}