@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Admin/work_day_list.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('title', '日勤一覧')

@section('content')
	<div class="container">
		@php
			$work_year = substr($work_day, 0, 4);
			$work_month = intval(substr($work_day, 5, 2));
			$work_date = intval(substr($work_day, 8, 2));
			$work_day_title = $work_year . "年" . $work_month . "月" . $work_date . "日";
		@endphp
		<h1 class="page__title">{{$work_day_title}}の勤怠</h1>
		<form class="page-form" action="{{ route('admin.attendance.paginationDate') }}" method="post">
			@csrf
			<button class="btn btn--prevDate" name="prevDate">
				<i class="bi bi-arrow-left-short"></i>
				<span>前日</span>
			</button>
			<div class="page-form__calendar">
				<input type="hidden" name="work_day" value="{{$work_day}}">
				<i class="bi bi-calendar3"></i>
				<p class="page-form__date-text">{{$work_day}}</p>
			</div>
			<button class="btn btn--nextDate" name="nextDate">
				<span>翌日</span>
				<i class="bi bi-arrow-right-short"></i>
			</button>
		</form>
		<table>
			<tr>
				<th class="work-day-list__header">名前</th>
				<th class="work-day-list__header">出勤</th>
				<th class="work-day-list__header">退勤</th>
				<th class="work-day-list__header">休憩</th>
				<th class="work-day-list__header">合計</th>
				<th class="work-day-list__header">詳細</th>
			</tr>
			@foreach ($workTimes as $workTime)
				@php
					$workTimeId = $workTime->id;
					$clockIn = substr($workTime->clock_in_time, 11, 5);
					$clockOut = substr($workTime->clock_out_time, 11, 5);

					foreach ($attendances as $attendance) {
						$attendance_workTimeId = $attendance->work_time_id;
						if($attendance_workTimeId == $workTimeId) {
							$total_break_time = substr($attendance->total_break_time, 0, 5);
							$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
							break;
						}
					}
				@endphp
				<tr>
					<td class="work-day-list__data">{{$workTime->user->name}}</td>
					<td class="work-day-list__data">{{$clockIn}}</td>
					<td class="work-day-list__data">{{$clockOut}}</td>
					<td class="work-day-list__data">{{$total_break_time}}</td>
					<td class="work-day-list__data">{{$actual_working_hours}}</td>
					<td class="work-day-list__data">
						<a class="work-day-list__detail" href="{{ route('admin.correctionRequest.create', $workTime) }}">詳細</a>
					</td>
				</tr>
			@endforeach
		</table>
	</div>
@endsection