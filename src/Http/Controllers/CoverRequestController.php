<?php

namespace Nukeflame\Webmatics\Http\Controllers;

use App\Models\CoverRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CoverRequestController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('monit:monitoring.dashboard');
    }

    public function metrics(Request $request): JsonResponse
    {
        $hours = (int) $request->query('hours', 24);
        $base  = CoverRequest::lastHours($hours);

        $total   = (clone $base)->count();
        $errors  = (clone $base)->errors()->count();
        $avgRt   = (int) round((clone $base)->avg('response_time') ?? 0);
        $p95     = $this->percentile(clone $base, 95);

        $perServer = (clone $base)
            ->selectRaw('server_id, COUNT(*) as total, ROUND(AVG(response_time)) as avg_rt, SUM(status_code >= 400) as errors')
            ->groupBy('server_id')
            ->get()
            ->keyBy('server_id');

        $requestsPerHour = (clone $base)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, server_id, COUNT(*) as total")
            ->groupBy('hour', 'server_id')
            ->orderBy('hour')
            ->get()
            ->groupBy('server_id');

        $statusBreakdown = (clone $base)
            ->selectRaw('status_code, COUNT(*) as total')
            ->groupBy('status_code')
            ->orderBy('status_code')
            ->pluck('total', 'status_code');

        return response()->json([
            'summary' => [
                'total_requests'    => $total,
                'avg_response_time' => $avgRt,
                'p95_response_time' => $p95,
                'error_count'       => $errors,
                'error_rate'        => $total > 0 ? round(($errors / $total) * 100, 1) : 0.0,
                'active_servers'    => (clone $base)->distinct('server_id')->count('server_id'),
            ],
            'per_server'        => $perServer,
            'requests_per_hour' => $requestsPerHour,
            'status_breakdown'  => $statusBreakdown,
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $query = CoverRequest::latest();

        if ($server = $request->query('server')) {
            $query->forServer($server);
        }

        if ($request->boolean('errors_only')) {
            $query->errors();
        }

        if ($request->boolean('slow_only')) {
            $query->slow();
        }

        $perPage = config('server-monitor.per_page', 50);

        $records = $query
            ->select(['id', 'server_id', 'url', 'method', 'client_ip', 'status_code', 'response_time', 'user_id', 'created_at'])
            ->paginate($perPage);

        return response()->json($records);
    }

    private function percentile($query, int $pct): int
    {
        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $offset = (int) floor($total * ($pct / 100));

        $value = (clone $query)
            ->orderBy('response_time')
            ->skip(max(0, $offset - 1))
            ->value('response_time');

        return (int) ($value ?? 0);
    }
}
