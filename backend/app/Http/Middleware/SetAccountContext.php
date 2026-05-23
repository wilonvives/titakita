<?php

namespace TitaKita\Http\Middleware;

use Closure;
use TitaKita\Models\User;
use Illuminate\Support\Facades\Auth;

class SetAccountContext
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $accountId = Auth::payload()->get('account_id');

            if ($accountId) {
                User::setCurrentAccountId($accountId);
            }
        }

        return $next($request);
    }
}
