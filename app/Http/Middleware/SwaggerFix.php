<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 2019-05-30
 * Time: 10:51
 */

namespace App\Http\Middleware;


class SwaggerFix
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
        if (strpos($request->headers->get("Authorization"),"Bearer ") === false) {
            $request->headers->set("Authorization","Bearer ".$request->headers->get("Authorization"));
        }

        $response = $next($request);

        return $response;
    }
}