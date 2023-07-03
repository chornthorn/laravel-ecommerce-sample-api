<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->header('Content-Type', 'application/json');

        $content = json_decode($response->getContent(), true);
        $response->setContent(json_encode([
            'message' => $response->status() === 200 ? 'success' : 'error',
            'status' => $response->status(),
            'data' => $content,
        ]));

        return $response;
    }
}
