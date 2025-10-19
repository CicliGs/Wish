<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait for error handling and logging
 */
trait ErrorHandlingTrait
{
    /**
     * Execute operation with error handling and logging
     */
    protected function withErrorHandling(callable $operation, string $errorMessage, array $context = []): mixed
    {
        try {
            return $operation();
        } catch (Exception $e) {
            $this->logError($errorMessage, array_merge($context, ['error' => $e->getMessage()]));
            return null;
        }
    }

    /**
     * Log error with context
     */
    protected function logError(string $message, array $context = []): void
    {
        $className = class_basename(static::class);
        Log::error("$className: $message", $context);
    }
}
