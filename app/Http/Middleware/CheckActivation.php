<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActivation
{

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user && $user->verified){
            return $next($request);
        }
        return
            response()->json([
               'status' => false,
              'message' => 'Please activate your account first.'
            ]);
    }
}
