@extends('Layouts.header')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/Admin/staff_list.css') }}">
@endsection

@section('title', 'スタッフ一覧')

@section('content')
	<div class="container">
		<h1 class="page__title">スタッフ一覧</h1>
		<table class="staff-list__table">
			<tr>
				<th class="staff-list__header">名前</th>
				<th class="staff-list__header">メールアドレス</th>
				<th class="staff-list__header">月次勤怠</th>
			</tr>
			@foreach ($users as $user)
				<tr>
					<td class="staff-list__data">{{$user->name}}</td>
					<td class="staff-list__data">{{$user->email}}</td>
					<td class="staff-list__data">
						<a class="staff-list__detail" href="{{ route('admin.attendance.staffAttendance', $user) }}">詳細</a>
					</td>
				</tr>
			@endforeach
		</table>
	</div>
@endsection