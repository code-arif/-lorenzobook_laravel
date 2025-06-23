<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CustomRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->status == 'active') {
            if (Auth::guard('web')->user()->hasRole('developer')) {
                return redirect()->intended(route('developer.dashboard', absolute: false));
            }elseif (Auth::guard('web')->user()->hasRole('admin')) {
                Log::info('Admin user logged in', ['user_id' => Auth::guard('web')->user()->id]);
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }else{
                Auth::logout();
                return redirect()->intended(route('login', absolute: false));
            }
        }

        return redirect()->intended(route('home', absolute: false));
    }
}
