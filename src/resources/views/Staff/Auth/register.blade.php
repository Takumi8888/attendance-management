@extends('Layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Staff/Auth/register.css') }}">
@endsection

@section('title', '会員登録')

@section('content')
	<div class="container">
		<h1 class="page__title">会員登録</h1>
		<form class="register-form" action="/register" method="post">
			@csrf
			{{-- ユーザー名 --}}
			<div class="register-form__group">
				<label for="name">ユーザー名</label>
				<input id="name" type="text" name="name" placeholder="山田太郎" value="{{ old('name') }}">
				<div class="register-form__error">
					@error('name') {{ $message }} @enderror
				</div>
			</div>
			{{-- メールアドレス --}}
			<div class="register-form__group">
				<label for="email">メールアドレス</label>
				<input id="email" type="email" name="email" placeholder="sample@example.com" value="{{ old('email') }}">
				<div class="register-form__error">
					@error('email') {{ $message }} @enderror
				</div>
			</div>
			{{-- パスワード --}}
			<div class="register-form__group">
				<label for="password">パスワード</label>
				<input id="password" type="password" name="password" placeholder="password" value="{{ old('password') }}">
				<div class="register-form__error">
					@error('password') {{ $message }} @enderror
				</div>
			</div>
			{{-- パスワード確認 --}}
			<div class="register-form__group">
				<label for="password_confirmation">パスワード確認</label>
				<input id="password_confirmation" type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
				<div class="register-form__error">
					@error('password_confirmation') {{ $message }} @enderror
				</div>
			</div>
			{{-- ボタン --}}
			<button class="btn btn--auth">登録する</button>
			<a class="link" href="/login">ログインはこちら</a>
		</form>
	</div>
@endsection