<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
	<link rel="stylesheet" href="{{ asset('css/Layouts/header.css') }}">
	@yield('css')
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	@yield('js')
	<title>@yield('title')</title>
</head>

<body>
	<header class="header">
		<div class="header__logo">
		@if(Auth::check())
			<img src="{{ asset('img/logo.svg') }}" alt="ロゴ">
		@elseif(Auth::check() == false)
			<a href="{{ route('admin.login.create') }}">
			<img src="{{ asset('img/logo.svg') }}" alt="ロゴ">
			</a>
		@endif
		</div>
		{{-- 管理者 --}}
		@if(Auth::guard('admins')->check())
			<nav class="header__nav">
				<ul>
					<li><a href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
					<li><a href="{{ route('admin.attendance.staff') }}">スタッフ一覧</a></li>
					<li><a href="{{ route('admin.correctionRequest.pendingApproval') }}">申請一覧</a></li>
					<form action="{{ route('admin.login.destroy') }}" method="post">
						@csrf
						<button class="header__logout">ログアウト</button>
					</form>
				</ul>
			</nav>
		{{-- スタッフ --}}
		@elseif(Auth::check())
			<nav class="header__nav">
				<ul>
					<li><a href="{{ route('attendanceRegister.index') }}">勤怠</a></li>
					<li><a href="{{ route('attendance.index') }}">勤怠一覧</a></li>
					<li><a href="{{ route('correctionRequest.pendingApproval') }}">申請</a></li>
					<form action="{{ route('login.destroy') }}" method="post">
						@csrf
						<button class="header__logout">ログアウト</button>
					</form>
				</ul>
			</nav>
		@endif
	</header>

	<main>
		@yield('content')
	</main>

</body>

</html>