<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JWTAuth extends BaseMiddleware
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
        try {
            // Authenticate the user with the 'member' guard
            $this->authenticate($request);

            // Check if the user is active
            if (auth()->guard('member')->user()->status !== 1) {
                auth()->guard('member')->logout();
                return response()->json(['message' => __('message.account_block')], 403);
            }
        } catch (TokenExpiredException $e) {
            // Handle expired token exception
            return response()->json(['message' => $e->getMessage()], 401);
        } catch (TokenInvalidException $e) {
            // Handle invalid token exception
            return response()->json(['message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}