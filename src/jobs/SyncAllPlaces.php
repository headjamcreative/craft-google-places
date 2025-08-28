<?php

namespace headjam\craftgoogleplaces\jobs;

use Craft;
use craft\queue\BaseJob;

/**
 * Sync All Places queue job
 */
class SyncAllPlaces extends BaseJob
{
    function execute($queue): void
    {
        // ...
    }

    protected function defaultDescription(): ?string
    {
        return null;
    }
}
