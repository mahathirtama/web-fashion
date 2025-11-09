<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle($request, Closure $next)
{
    
    // JANGAN blokir route login
    if ($request->routeIs('login') || $request->routeIs('login.post')) {

        return $next($request);
    }

    // Jika session belum login -> redirect
    if (!session('logged_in')) {
        
        return redirect()->route('login');
    }


    return $next($request);
}

}
