<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService;

class ClearPageCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:clear-pages {--all : Clear all page cache}';

    /**
     * The console command description.
     */
    protected $description = 'Clear page cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            try {
                Cache::flush();
                $this->info('All cache cleared successfully.');
            } catch (\Exception $e) {
                $this->error('Failed to clear all cache: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->info('Clearing page cache...');
            
            try {
                if (config('cache.default') === 'redis') {
                    $this->warn('Note: Redis pattern clearing is limited. Consider using --all option for complete cache clearing.');
                }
                
                $keys = Cache::get('page_cache:*');
                if ($keys) {
                    Cache::forget($keys);
                }
                
                $this->info('Page cache cleared successfully.');
            } catch (\Exception $e) {
                $this->error('Failed to clear page cache: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
} 