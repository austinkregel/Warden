<?php

/**
 * This is the default laravel authentication middleware,
 * it is only included incase someone doesn't have any
 * kind of authentication middleware. I am strongly
 * encouraging anyone and everyone to use their very own,
 * custom middleware and to not use this middleware for
 * customization!
 */

namespace Kregel\Warden\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Route;
class Authentication
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                if(Route::has(config('warden.auth.route')))
                  return redirect()->intended(route(config('warden.auth.route')));
                else
                  return redirect()->intended(url(config('warden.auth.fail_over_route')));
            }
        }

        return $next($request);
    }
}
