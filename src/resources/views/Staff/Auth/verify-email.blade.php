@extends('Layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Staff/Auth/verify-email.css') }}">
@endsection

@section('title', 'メール認証')

@section('content')
	<div class="container">
		{{-- メッセージ --}}
		<div class="mail__text">
			<p>登録していただいたメールアドレスに認証メールを送付しました。
			<br/>メール認証を完了してください。</p>
		</div>
		{{-- 認証ボタン --}}
		<div class="verification">
			<a class="verification-button" href="http://localhost:8025/">認証はこちらから</a>
		</div>
		{{-- 再送ボタン --}}
		<form class="mail-form__resend" action="{{ route('verification.send') }}" method="post">
			@csrf
			<button class="mail-form__resend-button" type="submit">認証メールを再送する</button>
		</form>
	</div>
@endsection