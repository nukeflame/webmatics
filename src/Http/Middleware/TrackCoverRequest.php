<?php

namespace Acentria\ServerMonitor\Http\Middleware;

use App\Models\CoverRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackCoverRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $start = microtime(true);

        $response = $next($request);

        $responseTime = (int) round((microtime(true) - $start) * 1000);

        if (! $this->isExcludedStatus($response->getStatusCode())) {
            $this->record($request, $response, $responseTime);
        }

        return $response;
    }

    private function record(Request $request, Response $response, int $responseTime): void
    {
        try {
            CoverRequest::create([
                'server_id'     => config('monit.server_id'),
                'url'           => $request->fullUrl(),
                'method'        => $request->method(),
                'client_ip'     => $request->ip(),
                'status_code'   => $response->getStatusCode(),
                'response_time' => $responseTime,
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'user_id'       => $request->user()?->getKey(),
            ]);
        } catch (\Throwable) {
            // Never let monitoring break the application
        }
    }

    private function shouldSkip(Request $request): bool
    {
        $path = $request->path();

        foreach (config('monit.exclude_paths', []) as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function isExcludedStatus(int $status): bool
    {
        return in_array($status, config('monit.exclude_status_codes', []), true);
    }
}
