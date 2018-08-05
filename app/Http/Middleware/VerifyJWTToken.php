<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VerifyJWTToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) 
    {
        try 
        {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) 
            {
                return response()->json([
                    'status' => 0,
                    'message' => 'Failed to validating token.'
                ], 404);
            }
        } 
        catch (JWTException $e) 
        {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) 
            {
                return response()->json([
                    'status' => 0,
                    'message' => 'Token Expired.'
                ], $e->getStatusCode());
            } 
            else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) 
            {
                return response()->json([
                            'status' => 0,
                            'message' => 'Invalid Token.'
                ], $e->getStatusCode());
            } 
            else 
            {
                return response()->json([
                    'status' => 0,
                    'message' => 'Token is required.'
                ], 404);
            }
        }
        return $next($request);
    }

}
