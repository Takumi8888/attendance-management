<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
		$this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
			public function toResponse($request)
			{
				return redirect('/attendance');
			}
		});
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

		Fortify::registerView(function () {
			return view('Staff.Auth.register');
		});

		Fortify::loginView(function () {
			return view('Staff.Auth.login');
		});

		Fortify::verifyEmailView(function () {
			return view('Staff.Auth.verify-email');
		});

		RateLimiter::for('login', function (Request $request) {
			$email = (string) $request->email;
			return Limit::perMinute(10)->by($email . $request->ip());
		});

		app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
