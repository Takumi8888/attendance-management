<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
		Password::defaults(
			static fn()
			=> Password::min(8)
				->max(255)
		);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		Paginator::useBootstrap();
	}
}
