<?php

namespace Nukeflame\Webmatics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class MonitorController extends Controller
{
    public function terminal(Request $request): JsonResponse
    {
        $command = trim($request->input('command', ''));
        $cwd     = session('monit_cwd', base_path());

        if ($command === '') {
            return response()->json(['output' => '', 'cwd' => $cwd]);
        }

        if (preg_match('/^\s*cd(?:\s+(.*))?$/', $command, $m)) {
            $target  = trim($m[1] ?? '');
            $newPath = match (true) {
                $target === '' || $target === '~' => base_path(),
                str_starts_with($target, '/')    => $target,
                default                          => $cwd . '/' . $target,
            };

            $real = realpath($newPath);

            if ($real && is_dir($real)) {
                session(['monit_cwd' => $real]);
                return response()->json(['output' => '', 'cwd' => $real]);
            }

            return response()->json([
                'output' => "bash: cd: {$target}: No such file or directory",
                'cwd'    => $cwd,
                'exit'   => 1,
            ]);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $cwd);

        if (! is_resource($process)) {
            return response()->json(['output' => 'proc_open: failed to spawn process', 'cwd' => $cwd, 'exit' => 1]);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        return response()->json([
            'output' => rtrim($stdout . $stderr),
            'cwd'    => session('monit_cwd', base_path()),
            'exit'   => $exit,
        ]);
    }
}
