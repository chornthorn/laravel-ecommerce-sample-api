<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HelperMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set the 'Accept' header of the request to 'application/json'
        $request->headers->set('Accept', 'application/json');

        // Call the next middleware in the chain and get the response
        $response = $next($request);

        // If the response is an instance of JsonResponse, set its 'Content-Type' header to 'application/json'
        if ($response instanceof JsonResponse) {
            $response->header('Content-Type', 'application/json');
        }

        // Return the response
        return $response;

    }
}
