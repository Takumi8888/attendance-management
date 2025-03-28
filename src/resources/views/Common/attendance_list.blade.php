@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Common/attendance_list.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('title', '勤怠一覧')

@section('content')
	<div class="container">
		@if(Auth::guard('admins')->check())
			<h1 class="page__title">{{$user->name}}さんの勤怠</h1>
		@elseif(Auth::check())
			<h1 class="page__title">勤怠一覧</h1>
		@endif
		<form class="page-form" method="post"
		@if (Auth::guard('admins')->check()) action="{{ route('admin.attendance.paginationMonth', $user) }}"
		@elseif (Auth::check()) action="{{ route('attendance.paginationMonth') }}"
		@endif>
			@csrf
			<button class="btn btn--prevMonth" name="prevMonth">
				<i class="bi bi-arrow-left-short"></i>
				<span>前月</span>
			</button>
			<div class="page-form__calendar">
				<input type="hidden" name="work_month" value="{{$work_month}}">
				<i class="bi bi-calendar3"></i>
				<p class="page-form__month-text">{{$work_month}}</p>
			</div>
			<button class="btn btn--nextMonth" name="nextMonth">
				<span>翌月</span>
				<i class="bi bi-arrow-right-short"></i>
			</button>
		</form>
		<table>
			<tr>
				<th class="attendance-list__header"></th>
				<th class="attendance-list__header">日付</th>
				<th class="attendance-list__header">出勤</th>
				<th class="attendance-list__header">退勤</th>
				<th class="attendance-list__header">休憩</th>
				<th class="attendance-list__header">合計</th>
				<th class="attendance-list__header">詳細</th>
			</tr>
			@php
				$array = ["日","月","火","水","木","金","土"];
				$count_work_day = strtotime($first_day);
			@endphp
			@for ($i = 1; $i <= $last_day; $i++)
				@php
					$status = 0;
					$workDate = date('m / d', $count_work_day) . " (" . $array[date("w", $count_work_day)] . ")";
				@endphp
				@foreach ($workTimes as $workTime)
					@php
						$workTimeId = $workTime->id;
						$clockIn = substr($workTime->clock_in_time, 11, 5);
						$clockOut = substr($workTime->clock_out_time, 11, 5);

						foreach ($attendances as $attendance) {
							$attendance_workTimeId = $attendance->work_time_id;
							if($attendance_workTimeId == $workTimeId) {
								$attendanceDate = strtotime($attendance->work_day);
								$check_day = date('d', $attendanceDate);
								$date = date('m / d', $attendanceDate)." (".$array[date("w", $attendanceDate)].")";
								$total_break_time = substr($attendance->total_break_time, 0, 5);
								$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
								break;
							}
						}
					@endphp
					@if (isset($check_day))
						@if (($i == $check_day))
							<tr>
								<td class="attendance-list__data"></td>
								<td class="attendance-list__data">{{$date}}</td>
								<td class="attendance-list__data">{{$clockIn}}</td>
								<td class="attendance-list__data">{{$clockOut}}</td>
								<td class="attendance-list__data">{{$total_break_time}}</td>
								<td class="attendance-list__data">{{$actual_working_hours}}</td>
								<td class="attendance-list__data">
									<a class="attendance-list__detail"
									@if (Auth::guard('admins')->check()) href="{{ route('admin.correctionRequest.create', $workTime) }}"
									@elseif (Auth::check() && empty($actual_working_hours)) href="{{ route('attendance.index') }}"
									@elseif (Auth::check() && isset($actual_working_hours)) href="{{ route('correctionRequest.create', $workTime) }}"
									@endif>詳細</a>
								</td>
							</tr>
							@php $status = 1; @endphp
						@endif
					@endif
				@endforeach
				@if (isset($check_day))
					@if (($i != $check_day)&& ($status == 0))
						<tr>
							<td class="attendance-list__data"></td>
							<td class="attendance-list__data">{{$workDate}}</td>
							<td class="attendance-list__data"></td>
							<td class="attendance-list__data"></td>
							<td class="attendance-list__data"></td>
							<td class="attendance-list__data"></td>
							<td class="attendance-list__data"></td>
						</tr>
					@endif
				@elseif (empty($check_day))
					<tr>
						<td class="attendance-list__data"></td>
						<td class="attendance-list__data">{{$workDate}}</td>
						<td class="attendance-list__data"></td>
						<td class="attendance-list__data"></td>
						<td class="attendance-list__data"></td>
						<td class="attendance-list__data"></td>
						<td class="attendance-list__data"></td>
					</tr>
				@endif
				@php $count_work_day += 86400; @endphp
			@endfor
		</table>
		@if(Auth::guard('admins')->check())
			<form class="export-form" action="{{ route('admin.attendance.export', $user)}}" method="post">
			@csrf
				<input type="hidden" name="work_month" value="{{$work_month}}">
				<button class="btn btn--CSV" type="submit">CSV出力</button>
			</form>
		@endif
	</div>
@endsection