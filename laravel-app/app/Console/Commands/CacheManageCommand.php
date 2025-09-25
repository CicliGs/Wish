<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheManagerService;
use App\Services\CacheType;
use Mockery\Exception;

class CacheManageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:manage
                            {action : Action to perform (clear|stats|clear-type|clear-user)}
                            {--type= : Cache type to clear (static_content|images|css_js|avatars)}
                            {--user= : User ID to clear cache for}';

    /**
     * The console command description.
     */
    protected $description = 'Manage application cache';

    public function __construct(
        protected CacheManagerService $cacheManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'clear' => $this->clearAllCache(),
            'stats' => $this->showCacheStats(),
            'clear-type' => $this->clearCacheByType(),
            'clear-user' => $this->clearUserCache(),
            default => $this->handleUnknownAction($action),
        };
    }

    /**
     * Handle unknown action
     */
    private function handleUnknownAction(string $action): int
    {
        $this->error("Unknown action: $action");
        return self::FAILURE;
    }

    /**
     * Clear all cache
     */
    private function clearAllCache(): int
    {
        $this->info('Clearing all cache...');

        if ($this->cacheManager->clearAllCache()) {
            $this->info('All cache cleared successfully!');
            return self::SUCCESS;
        } else {
            $this->error('Failed to clear cache!');
            return self::FAILURE;
        }
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(): int
    {
        $this->info('Cache Statistics:');
        $this->newLine();

        $stats = $this->cacheManager->getCacheStats();

        if (empty($stats)) {
            $this->error('Failed to get cache statistics!');
            return self::FAILURE;
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

        return self::SUCCESS;
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
            return self::FAILURE;
        }

        try {
            $cacheType = CacheType::from($type);
        } catch (Exception) {
            $this->error("Invalid cache type: $type");
            $this->info('Available types: static_content, images, css_js, avatars');
            return self::FAILURE;
        }

        $this->info("Clearing $type cache...");

        $success = $this->cacheManager->clearCacheByType($cacheType);

        if ($success) {
            $this->info("$type cache cleared successfully!");
            return self::SUCCESS;
        } else {
            $this->error("Failed to clear $type cache!");
            return self::FAILURE;
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
            return self::FAILURE;
        }

        if (!is_numeric($userId)) {
            $this->error('User ID must be a number');
            return self::FAILURE;
        }

        $this->info("Clearing cache for user $userId...");

        $success = $this->cacheManager->clearUserCache((int) $userId);

        if ($success) {
            $this->info("Cache for user $userId cleared successfully!");
            return self::SUCCESS;
        } else {
            $this->error("Failed to clear cache for user $userId!");
            return self::FAILURE;
        }
    }
}
