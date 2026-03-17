<?php

namespace Nukeflame\Webmatics\Console\Commands;

use App\Models\CoverRequest;
use Illuminate\Console\Command;

class PruneRequestLogs extends Command
{
    protected $signature   = 'monit:prune {--days= : Override retention days}';
    protected $description = 'Delete server monitor request logs older than the configured retention period.';

    public function handle(): int
    {
        $days    = (int) ($this->option('days') ?? config('monit.retention_days', 30));
        $cutoff  = now()->subDays($days);
        $deleted = CoverRequest::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} request record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
