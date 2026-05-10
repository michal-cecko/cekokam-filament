<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyStreamServerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.stream_server.token');

        if ($expected === '') {
            abort(500, 'Stream server token not configured.');
        }

        $provided = (string) $request->bearerToken();

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(401);
        }

        return $next($request);
    }
}
