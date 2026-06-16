<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PortfolioItem;

class PortfolioItemObserver
{
    /**
     * Delete child images through Eloquent before the DB cascade fires,
     * so PortfolioImageObserver::deleted() can clean up files on disk.
     */
    public function deleting(PortfolioItem $item): void
    {
        $item->images()->each(fn (mixed $image) => $image->delete());
    }
}
