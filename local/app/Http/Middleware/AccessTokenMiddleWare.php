<?php

namespace App\Http\Middleware;

use Closure;

define("ACCESS_TOKEN","fpLsXO6JsSBz0s71YhCMT6IkRgkMTalGR73g5qbC",true);
class AccessTokenMiddleWare
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
        if ($request->input('access_token') != ACCESS_TOKEN) {
            return response()->json(['status'=>'400','message'=>'Invalid access token!']);
        }

        return $next($request);
    }
}
