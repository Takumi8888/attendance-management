@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Common/request_list.css') }}">
@endsection

@section('title', '申請一覧')

@section('content')
	<div class="container">
		<h1 class="page__title">申請一覧</h1>
		{{-- タグ --}}
		<div class="page__tag">
            <div class="a page__tag--pending-approval">
				<a class="btn--pending-approval-{{ $page == 'pending_approval' ? 'on' : 'off' }}"
				@if(isset($admin)) href="{{ route('admin.correctionRequest.pendingApproval') }}"
				@elseif(empty($admin)) href="{{ route('correctionRequest.pendingApproval') }}"
				@endif>
					{!! $page == 'pending_approval' ? '承認待ち' : '承認待ち' !!}
				</a>
			</div>
			<form id="page-form" class="page-form" method="post" onsubmit="return false;"
			@if(isset($admin)) action="{{ route('admin.correctionRequest.approval') }}"
			@elseif(empty($admin)) action="{{ route('correctionRequest.approval') }}"
			@endif>
				@csrf
				<input type="hidden" name="$page" value="approval">
				<button class="btn btn--approval-{{ $page == 'approval' ? 'on' : 'off' }}" type="button" onclick="submit();">
					{!! $page == 'approval' ? '承認済み' : '承認済み' !!}
				</button>
			</form>
		</div>
		{{-- テーブル --}}
		<table>
			<tr>
				<th class="request-list__header"></th>
				<th class="request-list__header">状態</th>
				<th class="request-list__header">名前</th>
				<th class="request-list__header">対象日時</th>
				<th class="request-list__header">申請理由</th>
				<th class="request-list__header">申請日時</th>
				<th class="request-list__header">詳細</th>
			</tr>
			{{-- 承認待ち --}}
			@if ($page == 'pending_approval')
				@foreach ($pendingApprovals as $pendingApproval)
					@php
						$name = App\Models\User::find($pendingApproval->user_id)->name;
						$id = $pendingApproval->attendance_id;
						$workTime = App\Models\WorkTime::find($id);

						$work_year = substr(App\Models\Attendance::find($id)->work_day, 0, 4);
						$work_month = substr(App\Models\Attendance::find($id)->work_day, 5, 2);
						$work_date = substr(App\Models\Attendance::find($id)->work_day, 8, 2);
						$work_day = $work_year . "/" . $work_month . "/" . $work_date;

						$application_year = substr($pendingApproval->application_date, 0, 4);
						$application_month = substr($pendingApproval->application_date, 5, 2);
						$application_date = substr($pendingApproval->application_date, 8, 2);
						$application_day = $application_year . "/" . $application_month . "/" . $application_date;
					@endphp
					<tr>
						<td class="request-list__data"></td>
						@if ($pendingApproval->status == 1)
							<td class="request-list__data">承認待ち</td>
						@else
							<td class="request-list__data">承認済み</td>
						@endif
						<td class="request-list__data">{{$name}}</td>
						<td class="request-list__data">{{$work_day}}</td>
						<td class="request-list__data">
							<p class="note">{{$pendingApproval->note}}</p>
						</td>
						<td class="request-list__data">{{$application_day}}</td>
						<td class="request-list__data">
							<a class="request-list__detail"
							@if(isset($admin)) href="{{ route('admin.correctionRequest.edit', $workTime) }}"
							@elseif(empty($admin)) href="{{ route('correctionRequest.create', $workTime) }}"
							@endif>詳細</a>
						</td>
					</tr>
				@endforeach
			{{-- 承認済み --}}
			@elseif ($page == 'approval')
				@foreach ($approvals as $approval)
					@php
						$name = App\Models\User::find($approval->user_id)->name;
						$id = $approval->attendance_id;
						$workTime = App\Models\WorkTime::find($id);

						$work_year = substr(App\Models\Attendance::find($id)->work_day, 0, 4);
						$work_month = substr(App\Models\Attendance::find($id)->work_day, 5, 2);
						$work_date = substr(App\Models\Attendance::find($id)->work_day, 8, 2);
						$work_day = $work_year . "/" . $work_month . "/" . $work_date;

						$application_year = substr($approval->application_date, 0, 4);
						$application_month = substr($approval->application_date, 5, 2);
						$application_date = substr($approval->application_date, 8, 2);
						$application_day = $application_year . "/" . $application_month . "/" . $application_date;
					@endphp
					<tr>
						<td class="request-list__data"></td>
						@if ($approval->status == 1)
							<td class="request-list__data">承認待ち</td>
						@else
							<td class="request-list__data">承認済み</td>
						@endif
						<td class="request-list__data">{{$name}}</td>
						<td class="request-list__data">{{$work_day}}</td>
						<td class="request-list__data">
							<p class="note">{{$approval->note}}</p>
						</td>
						<td class="request-list__data">{{$application_day}}</td>
						<td class="request-list__data">
							<a class="request-list__detail"
							@if(isset($admin)) href="{{ route('admin.correctionRequest.approved', $workTime) }}"
							@elseif(empty($admin)) href="{{ route('correctionRequest.create', $workTime) }}"
							@endif>詳細</a>
						</td>
					</tr>
				@endforeach
			@endif
		</table>
		{{-- ページネーション --}}
		@if (isset($pendingApprovals))
			{{ $pendingApprovals->appends(request()->query())->links('vendor.pagination.custom') }}
		@elseif (isset($approvals))
			{{ $approvals->appends(request()->query())->links('vendor.pagination.custom') }}
		@endif
	</div>
@endsection