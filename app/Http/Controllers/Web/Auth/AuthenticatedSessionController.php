<?php
namespace App\Http\Controllers\Web\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Middleware\CustomRedirectMiddleware;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                $request->authenticate();

                $request->session()->regenerate();
                return redirect()->intended(route('developer.dashboard', absolute: false));

                // session()->put('t-success', 'Password Confirmed Successfully');
                // Log::info('User logged in successfully', ['email' => $request->email]);
                // return app(CustomRedirectMiddleware::class)->handle($request, function () {});

            } else {
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
            }
        } catch (\Throwable $th) {
            return back()->withErrors([
                'email' => 'An error occurred while trying to log in. Please try again later.',
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        session()->put('t-success', 'Logout Successfully');

        return redirect('/');
    }
}
