<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        throwJson(['message'=>'Unauthorized!'],401);

        if (! $request->expectsJson() && !$request->wantsJson()) {
            return route('login');
        }
    }
}
