<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $res = $next($request);
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $res
                ->header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN'])
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type')
                ->header('Access-Control-Allow-Credentials', 'true');
        }
        return $res;
    }
}
