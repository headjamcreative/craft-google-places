<?php

namespace headjam\craftgoogleplaces\jobs;

use Craft;
use craft\queue\BaseJob;
use headjam\craftgoogleplaces\CraftGooglePlaces;
use headjam\craftgoogleplaces\fields\GooglePlacesSync as GooglePlacesSyncField;

/**
 * Sync All Places queue job
 */
class SyncAllPlaces extends BaseJob
{
    function execute($queue): void
    {
        $allFields = CraftGooglePlaces::getInstance()->fields->getAllFields('global');
        $searchQuery = '';
        foreach($allFields as $field) {
          if ($field instanceof GooglePlacesSyncField) {
            $searchQuery = ' OR ' . $field->handle . ':*';
          }
        }
        $searchQuery = preg_replace('/ OR /', '', $searchQuery, 1);
        $entries = strlen($searchQuery) ? \craft\elements\Entry::find()->search($searchQuery)->unique()->all() : [];
        $totalEntries = count($entries);

        foreach($entries as $i => $entry) {
          $this->setProgress(
            $queue,
            $i / $totalEntries,
            Craft::t('craft-google-places', '{step, number} of {total, number}', [
              'step' => $i + 1,
              'total' => $totalEntries,
            ])
          );

          // Triggers the sync process
          try {
            Craft::$app->elements->saveElement($entry);
          } catch (\Throwable $e) {
            Craft::error('Google Places sync failed for entry ID ' . $entry->id . ': ' . $e->getMessage(), 'craft-google-places');
          }
        }
    }

    protected function defaultDescription(): ?string
    {
        return Craft::t('craft-google-places', 'Syncing Google Places data');
    }
}
