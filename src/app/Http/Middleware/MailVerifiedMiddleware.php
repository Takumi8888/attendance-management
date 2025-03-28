<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MailVerifiedMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next)
	{
		$session_data = session()->get('unauthenticated_user') ?? null;

		if ($session_data) {
			return redirect()->route('verification.notice');
		}

		return $next($request);
	}
}
