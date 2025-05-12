@extends('Layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Common/Auth/login.css') }}">
@endsection

@section('title', 'ログイン')

@section('content')
	<div class="container">
		<h1 class="page__title">ログイン</h1>
		<form class="login-form" action="{{ route('login.store') }}" method="post">
			@csrf
			{{-- メールアドレス --}}
			<div class="login-form__group">
				<label for="email">メールアドレス</label>
				<input id="email" type="email" name="email" value="{{ old('email') }}">
				<div class="login-form__error">
					@error('email') {{ $message }} @enderror
				</div>
			</div>
			{{-- パスワード --}}
			<div class="login-form__group">
				<label for="password">パスワード</label>
				<input id="password" type="password" name="password" value="{{ old('password') }}">
				<div class="login-form__error">
					@error('password') {{ $message }} @enderror
				</div>
			</div>
			{{-- ボタン --}}
			<button class="btn btn--auth">ログインする</button>
			<a class="link" href="/register">会員登録はこちら</a>
		</form>
	</div>
@endsection