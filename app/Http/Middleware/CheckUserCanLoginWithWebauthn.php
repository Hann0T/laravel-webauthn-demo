<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckUserCanLoginWithWebauthn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $email = $request->cookie('email');
        $user = User::where('email', $email)->get()->first();

        if ($user && $user->webauthnKeys()->count() > 0) {
            return redirect(route('webauthn.login'));
        }

        return $next($request);
    }
}
