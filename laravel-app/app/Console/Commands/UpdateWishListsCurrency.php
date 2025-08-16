<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\WishList;
use Illuminate\Console\Command;

class UpdateWishListsCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wishlists:update-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing wish lists with default currency';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info(__('messages.updating_wishlists_currency'));

        $updatedCount = WishList::whereNull('currency')
            ->orWhere('currency', '')
            ->update(['currency' => WishList::DEFAULT_CURRENCY]);

        $this->info(__('messages.wishlists_currency_updated', [
            'count' => $updatedCount,
            'currency' => WishList::DEFAULT_CURRENCY
        ]));

        return Command::SUCCESS;
    }
}
