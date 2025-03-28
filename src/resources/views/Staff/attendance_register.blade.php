@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Staff/attendance_register.css') }}">
@endsection

@section('title', '勤怠')

@section('content')
	<div class="container">
		<div class="attendance__status">
			@if ($status == 0)
				<p class="attendance__status-tag">勤務外</p>
			@elseif ($status == 1)
				<p class="attendance__status-tag">出勤中</p>
			@elseif ($status == 2)
				<p class="attendance__status-tag">休憩中</p>
			@elseif ($status == 3)
				<p class="attendance__status-tag">退勤済</p>
			@endif
		</div>
		<div class="attendance__date" id="date"></div>
		<div class="attendance__time" id="time"></div>
		<div class="attendance__button">
			@if ($status == 0)
				<form class="attendance-form" action="{{ route('attendanceRegister.clockIn') }}" method="post">
					@csrf
					<button class="btn btn--clock-in-time">出勤</button>
				</form>
			@elseif ($status == 1)
				<div class="attendance__button-group">
					<form class="attendance-form__group" action="{{ route('attendanceRegister.clockOut') }}" method="post">
						@method('put')
						@csrf
						<input type="hidden" name="id" value="{{ $workTime->id }}">
						<button class="btn btn--clock-out-time">退勤</button>
					</form>
					<form class="attendance-form__group" action="{{ route('attendanceRegister.breakTimeStart') }}" method="post">
						@csrf
						<input type="hidden" name="id" value="{{ $workTime->id }}">
						<button class="btn btn--start-time">休憩入</button>
					</form>
				</div>
			@elseif ($status == 2)
				<form class="attendance-form" action="{{ route('attendanceRegister.breakTimeEnd') }}" method="post">
					@method('put')
					@csrf
					<input type="hidden" name="id" value="{{ $breakTime->id }}">
					<button class="btn btn--end-time">休憩戻</button>
				</form>
			@elseif ($status == 3)
				<p class="attendance-form__text">&emsp;お疲れ様でした。</p>
			@endif
		</div>
	</div>

<script src="{{ asset('js/Staff/attendance_register.js') }}"></script>
@endsection