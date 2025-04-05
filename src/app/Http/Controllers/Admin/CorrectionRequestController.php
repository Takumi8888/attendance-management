<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\WorkTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
	public function pendingApproval()
	{
		$page = 'pending_approval';
		$admin = Auth::user();
		$pendingApprovals = CorrectionRequest::where('status', 1)->paginate(15);

		return view('Common.request_list', compact('admin', 'page', 'pendingApprovals'));
	}

	public function approval()
	{
		$page = 'approval';
		$admin = Auth::user();
		$approvals = CorrectionRequest::where('status', 2)->paginate(15);

		return view('Common.request_list', compact('admin', 'page', 'approvals'));
	}

	public function create(WorkTime $workTime)
	{
		$admin = Auth::user();
		$user = User::find($workTime->user_id);
		$id = $workTime->id;
		$attendance = Attendance::find($id);
		$date = $attendance->date;
		$breakTimes = BreakTime::where('work_time_id', $id)->get();
		$correctionRequest = CorrectionRequest::where('attendance_id', $id)->first();

		return view('Common.request_details', compact('admin', 'user', 'attendance', 'workTime', 'breakTimes', 'correctionRequest'));
	}

	public function store(AttendanceRequest $request, WorkTime $workTime)
	{
		$user = User::find($workTime->user_id)->id;
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->get();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		//////////////////////////////////////////////////////////////////
		// 日付変更
		// $change_date = $request->change_date;

		//////////////////////////////////////////////////////////////////
		// 拘束時間
		$date = substr($workTime->clock_in_time, 0, 10);
		$clock_in_time = $date . " " . $request->clock_in_time . ":00";
		$clock_out_time = $date . " " . $request->clock_out_time . ":00";
		$startTime = strtotime($clock_in_time);
		$endTime = strtotime($clock_out_time);
		$timeDifference = $endTime - $startTime;
		$hours = floor($timeDifference / 3600);
		$minutes = floor(($timeDifference % 3600) / 60);
		$seconds = $timeDifference % 60;
		$actual_workTime = date($hours . ':' . $minutes . ':' . $seconds);

		$workTime->update([
			'user_id'        => $user,
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'work_time'      => $actual_workTime,
		]);

		//////////////////////////////////////////////////////////////////
		// 既存休憩時間変更
		$startTime = [];
		$endTime = [];
		$actual_breakTime = [];

		for ($i = 0; $i < count($breakTime); $i++) {
			$start_time[$i] = $date . " " . $request->start_time[$i] . ":00";
			$end_time[$i] = $date . " " . $request->end_time[$i] . ":00";
			$startTime[$i] = strtotime($start_time[$i]);
			$endTime[$i] = strtotime($end_time[$i]);
			$timeDifference = $endTime[$i] - $startTime[$i];
			$hours = floor($timeDifference / 3600);
			$minutes = floor(($timeDifference % 3600) / 60);
			$seconds = $timeDifference % 60;
			$actual_breakTime[$i] = date($hours . ':' . $minutes . ':' . $seconds);

			$breakTime[$i]->update([
				'start_time' => $start_time[$i],
				'end_time'   => $end_time[$i],
				'break_time' => $actual_breakTime[$i],
			]);
		}

		//////////////////////////////////////////////////////////////////
		// 追加休憩時間登録
		if (isset($request->add_start_time)) {
			$add_start_time = $date . " " . $request->add_start_time . ":00";
			$add_end_time = $date . " " . $request->add_end_time . ":00";
			$startTime = strtotime($add_start_time);
			$endTime = strtotime($add_end_time);
			$timeDifference = $endTime - $startTime;
			$hours = floor($timeDifference / 3600);
			$minutes = floor(($timeDifference % 3600) / 60);
			$seconds = $timeDifference % 60;
			$actual_breakTime = date($hours . ':' . $minutes . ':' . $seconds);

			BreakTime::create([
				'work_time_id' => $workTime->id,
				'start_time'   => $add_start_time,
				'end_time'     => $add_end_time,
				'break_time'   => $actual_breakTime,
			]);
		}

		//////////////////////////////////////////////////////////////////
		// 総休憩時間変更
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->get();
		$add_breakTime = 0;

		for ($i = 0; $i < count($breakTime); $i++) {
			$actual_breakTime = strtotime($date . " " . $breakTime[$i]->break_time);
			$difference_breakTime = $actual_breakTime - strtotime($date);
			$add_breakTime = $add_breakTime + $difference_breakTime;
		}

		$hours = floor($add_breakTime / 3600);
		$minutes = floor(($add_breakTime % 3600) / 60);
		$seconds = $add_breakTime % 60;
		$total_break_time = date($hours . ':' . $minutes . ':' . $seconds);

		//////////////////////////////////////////////////////////////////
		// 実労働時間変更
		$difference_workTime = strtotime($workTime->work_time) - strtotime($total_break_time);
		$hours = floor($difference_workTime / 3600);
		$minutes = floor(($difference_workTime % 3600) / 60);
		$seconds = $difference_workTime % 60;
		$actual_working_hours = date($hours . ':' . $minutes . ':' . $seconds);

		$attendance->update([
			'work_day'             => $date,
			'total_break_time'     => $total_break_time,
			'actual_working_hours' => $actual_working_hours,
		]);

		//////////////////////////////////////////////////////////////////
		// CorrectionRequestsテーブル追加
		CorrectionRequest::create([
			'attendance_id'    => $attendance->id,
			'user_id'          => $user,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 1,
			'note'             => $request->note,
		]);

		return redirect()->route('admin.correctionRequest.pendingApproval');
	}

	public function edit(WorkTime $workTime)
	{
		$admin = Auth::user();
		$user = User::find($workTime->user_id);
		$id = $workTime->id;
		$attendance = Attendance::find($id);
		$date = $attendance->date;
		$breakTimes = BreakTime::where('work_time_id', $id)->get();
		$correctionRequest = CorrectionRequest::where('attendance_id', $id)->first();

		return view('Common.request_details', compact('admin', 'user', 'attendance', 'workTime', 'breakTimes', 'correctionRequest'));
	}

	public function update(WorkTime $workTime)
	{
		$admin_id = Auth::user()->id;
		$correctionRequest = CorrectionRequest::where('attendance_id', $workTime->id)->first();

		$correctionRequest->update([
			'admin_id' => $admin_id,
			'status'   => 2,
		]);

		return redirect()->route('admin.correctionRequest.pendingApproval');
	}

	public function approved(WorkTime $workTime)
	{
		$admin = Auth::admin();
		$user = User::find($workTime->user_id);
		$id = $workTime->id;
		$attendance = Attendance::find($id);
		$date = $attendance->date;
		$breakTimes = BreakTime::where('work_time_id', $id)->get();
		$correctionRequest = CorrectionRequest::where('attendance_id', $id)->first();

		return view('Common.request_details', compact('admin', 'user', 'attendance', 'workTime', 'breakTimes', 'correctionRequest'));
	}
}