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

                Log::info('Admin user logged in', ['user_id' => Auth::guard('web')->user()->id]);
                return redirect()->intended(route('admin.dashboard', absolute: false));


        return redirect()->intended(route('home', absolute: false));
    }
}
