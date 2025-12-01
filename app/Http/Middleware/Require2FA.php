<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if(!$user){
            return response()->json(['message' => 'unauthenticated'],401);
        }
        if(!$user->two_factor_secret){
            return $next($request);
        }

        $twoFAValidatedAt = session('2fa_verified');
        if(!$twoFAValidatedAt || Carbon::parse($twoFAValidatedAt)->addMinutes(20)->isPast()){
            return response()->json(['message' => '2fa required.'], 403);
        }
        return $next($request);
    }
}
