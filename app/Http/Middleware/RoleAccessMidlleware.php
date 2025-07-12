<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleAccessMidlleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {

        if(!auth()->check()){
            return ResponseFormatter::error(null, 'Unauthorized', Response::HTTP_UNAUTHORIZED);
        }
        if($role !== auth()->user()->role){
            return ResponseFormatter::error(null, "Permission denied: '".auth()->user()->role."' role does not have access to this endpoint.", Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
