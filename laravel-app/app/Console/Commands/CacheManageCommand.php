<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use App\Services\CacheType;
use Mockery\Exception;

class CacheManageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:manage
                            {action : Action to perform (clear, stats, clear-type, clear-user)}
                            {--type= : Cache type to clear (static_content, images, css_js, avatars)}
                            {--user= : User ID to clear cache for}';

    /**
     * The console command description.
     */
    protected $description = 'Manage application cache';

    public function __construct(
        protected CacheService $cacheService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'clear':
                return $this->clearAllCache();
            case 'stats':
                return $this->showCacheStats();
            case 'clear-type':
                return $this->clearCacheByType();
            case 'clear-user':
                return $this->clearUserCache();
            default:
                $this->error("Unknown action: $action");
                return 1;
        }
    }

    /**
     * Clear all cache
     */
    private function clearAllCache(): int
    {
        $this->info('Clearing all cache...');

        $success = $this->cacheService->clearAllCache();

        if ($success) {
            $this->info('All cache cleared successfully!');
            return 0;
        } else {
            $this->error('Failed to clear cache!');
            return 1;
        }
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(): int
    {
        $this->info('Cache Statistics:');
        $this->newLine();

        $stats = $this->cacheService->getCacheStats();

        if (empty($stats)) {
            $this->error('Failed to get cache statistics!');
            return 1;
        }

        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', $stats['driver'] ?? 'N/A'],
                ['Store', $stats['store'] ?? 'N/A'],
                ['Prefix', $stats['prefix'] ?? 'N/A'],
            ]
        );

        $this->newLine();
        $this->info('TTL Settings:');

        foreach ($stats['ttl_settings'] ?? [] as $type => $ttl) {
            $this->line("  $type: $ttl seconds");
        }

        $this->newLine();
        $this->info($stats['description'] ?? 'No description available');

        return 0;
    }

    /**
     * Clear cache by type
     */
    private function clearCacheByType(): int
    {
        $type = $this->option('type');

        if (!$type) {
            $this->error('Please specify cache type with --type option');
            $this->info('Available types: static_content, images, css_js, avatars');
            return 1;
        }

        try {
            $cacheType = CacheType::from($type);
        } catch (Exception) {
            $this->error("Invalid cache type: $type");
            $this->info('Available types: static_content, images, css_js, avatars');
            return 1;
        }

        $this->info("Clearing $type cache...");

        $success = $this->cacheService->clearCacheByType($cacheType);

        if ($success) {
            $this->info("$type cache cleared successfully!");
            return 0;
        } else {
            $this->error("Failed to clear $type cache!");
            return 1;
        }
    }

    /**
     * Clear user cache
     */
    private function clearUserCache(): int
    {
        $userId = $this->option('user');

        if (!$userId) {
            $this->error('Please specify user ID with --user option');
            return 1;
        }

        if (!is_numeric($userId)) {
            $this->error('User ID must be a number');
            return 1;
        }

        $this->info("Clearing cache for user $userId...");

        $success = $this->cacheService->clearUserCache((int) $userId);

        if ($success) {
            $this->info("Cache for user $userId cleared successfully!");
            return 0;
        } else {
            $this->error("Failed to clear cache for user $userId!");
            return 1;
        }
    }
}
