<?php

namespace Nukeflame\Webmatics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MonitBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = config('monit.basic_auth.username');
        $password = config('monit.basic_auth.password');

        if (
            !$request->hasHeader('Authorization') ||
            !$this->isValid($request, $user, $password)
        ) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Monit Dashboard"',
            ]);
        }

        return $next($request);
    }

    protected function isValid(Request $request, string $user, string $password): bool
    {
        $decoded = base64_decode(substr($request->header('Authorization'), 6));
        [$u, $p] = explode(':', $decoded, 2);

        return hash_equals($user, $u) && hash_equals($password, $p);
    }
}
