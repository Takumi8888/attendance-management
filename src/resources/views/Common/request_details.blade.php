@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Common/request_details.css') }}">
@endsection

@section('title', '勤怠詳細')

@section('content')
	<div class="container">
		<h1 class="page__title">勤怠詳細</h1>
		<form class="request-form" method="post">
			@if ((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
			@elseif (((empty($correctionRequest->status)) && isset($admin))
			|| (($correctionRequest->status == 1) && isset($admin)))
				@method('put')
			@endif
			@csrf
			@php
				$year = intval(substr($attendance->work_day, 0, 4));
				$month = intval(substr($attendance->work_day, 5, 2));
				$date = intval(substr($attendance->work_day, 8, 2));
				$clock_in_time = substr($workTime->clock_in_time, 11, 5);
				$clock_out_time = substr($workTime->clock_out_time, 11, 5);
				$i = 0;
			@endphp
			<table>
				{{-- 名前 --}}
				<tr>
					<th class="request__header">名前</th>
					<td class="request__data-name">{{ $user->name }}</td>
				</tr>
				{{-- 日付 --}}
				<tr>
					<th class="request__header">日付</th>
					<td class="request__data">
						<div class="request__data-request-date">
							{{-- 日付変更（管理者画面） --}}
							{{-- @if ((empty($correctionRequest->status)) && isset($admin))
								<input class="request__data-date" type="date" name="change_date" value="{{ old('change_date') }}">
							@elseif (((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
							|| (($correctionRequest->status == 1) && empty($admin))
							|| (($correctionRequest->status == 1) && isset($admin))
							|| (($correctionRequest->status == 2) && isset($admin))) --}}
								<p class="request__data-year--display">{{ $year . "年"}}</p>
								<p class="request__data-date--display">{{ $month . "月" . $date . "日"}}</p>
							{{-- @endif --}}
						</div>
					</td>
				</tr>
				{{-- 出勤・退勤時間 --}}
				<tr>
					<th class="request__header">出勤・退勤</th>
					{{-- 編集可能 --}}
					@if (((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
					|| ((empty($correctionRequest->status)) && isset($admin)))
						<td class="request__data">
							<div class="request__data-time">
								<input class="request__clock-in-time" type="time" name="clock_in_time" value="{{ $clock_in_time }}">
								<span class="hyphen">～</span>
								<input class="request__clock-out-time" type="time" name="clock_out_time" value="{{ $clock_out_time }}">
							</div>
							<div class="request__error">
								@error ('clock_in_time') {{ $message }} @enderror
								@error ('clock_out_time') {{ $message }} @enderror
							</div>
						</td>
					{{-- 表示のみ --}}
					@elseif ((($correctionRequest->status == 1) && empty($admin))
					|| (($correctionRequest->status == 1) && isset($admin))
					|| (($correctionRequest->status == 2) && isset($admin)))
						<td class="request__data">
							<div class="request__data-time">
								<p class="request__clock-in-time--display">{{ $clock_in_time }}</p>
								<span class="request__hyphen--display">～</span>
								<p class="request__clock-out-time--display">{{ $clock_out_time }}</p>
							</div>
						</td>
					@endif
				</tr>
				{{-- 休憩時間 --}}
				@foreach ($breakTimes as $breakTime)
				@php
					$start_time = substr($breakTime->start_time, 11, 5);
					$end_time = substr($breakTime->end_time, 11, 5);
				@endphp
				<tr>
					<th class="request__header">{{"休憩" . $i + 1}}</th>
					{{-- 編集可能 --}}
					@if (((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
					|| ((empty($correctionRequest->status)) && isset($admin)))
						<td class="request__data">
							<div class="request__data-time">
								<input class="request__start-time" type="time" name="start_time[]" value="{{ $start_time }}">
								<span class="hyphen">～</span>
								<input class="request__end-time" type="time" name="end_time[]" value="{{ $end_time }}">
							</div>
							<div class="request__error">
								@error ('start_time') {{ $message }} @enderror
								@error ('start_time.' . $i) {{ $message }} @enderror
								@error ('end_time') {{ $message }} @enderror
								@error ('end_time.' . $i) {{ $message }} @enderror
							</div>
						</td>
					{{-- 表示のみ --}}
					@elseif ((($correctionRequest->status == 1) && empty($admin))
					|| (($correctionRequest->status == 1) && isset($admin))
					|| (($correctionRequest->status == 2) && isset($admin)))
						<td class="request__data">
							<div class="request__data-time">
								<p class="request__start-time--display">{{ $start_time }}</p>
								<span class="request__hyphen--display">～</span>
								<p class="request__end-time--display">{{ $end_time }}</p>
							</div>
						</td>
					@endif
				</tr>
				@php $i += 1; @endphp
				@endforeach
				<tr>
					<th class="request__header">{{"休憩" . $i + 1}}</th>
					{{-- 編集可能 --}}
					@if (((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
					|| ((empty($correctionRequest->status)) && isset($admin)))
						<td class="request__data">
							<div class="request__data-time">
								<input class="request__add-start-time" type="time" name="add_start_time" value="{{ old('add_start_time') }}">
								<span class="request__add-hyphen">～</span>
								<input class="request__add-end-time" type="time" name="add_end_time" value="{{ old('add_end_time') }}">
							</div>
							<div class="request__error">
								@error ('add_start_time') {{ $message }} @enderror
								@error ('add_end_time') {{ $message }} @enderror
							</div>
						</td>
					{{-- 表示のみ --}}
					@elseif ((($correctionRequest->status == 1) && empty($admin))
					|| (($correctionRequest->status == 1) && isset($admin))
					|| (($correctionRequest->status == 2) && isset($admin)))
						<td class="request__data"></td>
					@endif
				</tr>
				{{-- 備考 --}}
				<tr>
					<th class="request__header">備考</th>
					<td class="request__data">
						{{-- 編集可能 --}}
						@if (((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
						|| ((empty($correctionRequest->status)) && isset($admin)))
							<textarea class="request__note" name="note">{{ old('note') }}</textarea>
							<div class="request__error">
								@error('note') {{ $message }} @enderror
							</div>
						{{-- 表示のみ --}}
						@elseif ((($correctionRequest->status == 1) && empty($admin))
						|| (($correctionRequest->status == 1) && isset($admin))
						|| (($correctionRequest->status == 2) && isset($admin)))
							<p class="request__note--display">{{ $correctionRequest->note}}</p>
						@endif
					</td>
				</tr>
			</table>
			<div class="request-form__button">
				@if ((empty($correctionRequest->status) || ($correctionRequest->status == 2)) && empty($admin))
					<button class="btn btn--request" type="submit" formaction="{{ route('correctionRequest.store', $workTime) }}">修正</button>
				@elseif ((empty($correctionRequest->status)) && isset($admin))
					<button class="btn btn--request" type="submit" formaction="{{ route('admin.correctionRequest.store', $workTime) }}">修正</button>
				@elseif (($correctionRequest->status == 1) && isset($admin))
					<button class="btn btn--approve" type="submit" formaction="{{ route('admin.correctionRequest.update', $workTime) }}">承認</button>
				@elseif (($correctionRequest->status == 2) && isset($admin))
					<button class="btn btn--approved" type="button">承認済み</button>
				@elseif (($correctionRequest->status == 1) && empty($admin))
					<p class="request-form__message">※承認待ちのため修正はできません。</p>
				@endif
			</div>
		</form>
	</div>
@endsection