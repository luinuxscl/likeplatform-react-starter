<?php

namespace App\Console\Commands;

use App\Services\DashboardStatsService;
use Illuminate\Console\Command;

class ClearWidgetCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'widgets:cache:clear';

    /**
     * The console command description.
     */
    protected $description = 'Clear all widget data cache';

    /**
     * Execute the console command.
     */
    public function handle(DashboardStatsService $statsService): int
    {
        $this->info('Clearing widget data cache...');
        
        $statsService->clearCache();
        
        $this->info('✓ Widget data cache cleared successfully!');
        
        return Command::SUCCESS;
    }
}
