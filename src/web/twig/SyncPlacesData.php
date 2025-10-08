<?php

namespace headjam\craftgoogleplaces\web\twig;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use headjam\craftgoogleplaces\CraftGooglePlaces;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Twig extension
 */
class SyncPlacesData extends AbstractExtension
{
    public function getFilters()
    {
        // Define custom Twig filters
        // (see https://twig.symfony.com/doc/3.x/advanced.html#filters)
        return [
            // new TwigFilter('passwordify', function($string) {
            //     return strtr($string, [
            //         'a' => '@',
            //         'e' => '3',
            //         'i' => '1',
            //         'o' => '0',
            //         's' => '5',
            //     ]);
            // }),
        ];
    }

    public function getFunctions()
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            new TwigFunction('syncPlacesData', function(mixed $googlePlacesField) {
              if ($googlePlacesField && ($googlePlacesField['id'] ?? false || $googlePlacesField['lookup'] ?? false)) {
                $record = CraftGooglePlaces::getInstance()->googlePlacesPersist->findGooglePlaceData($googlePlacesField['id'] ?? null, $googlePlacesField['lookup'] ?? null);
              }

              $hours = isset($googlePlacesField['hours']) && is_array($googlePlacesField['hours']) ? $googlePlacesField['hours'] : [];

              return [
                'lookup' => $googlePlacesField['lookup'] ?? null,
                'id' => $googlePlacesField['id'] ?? null,
                'name' => $googlePlacesField['name'] ?? $record->displayName ?? null,
                'address' => $googlePlacesField['address'] ?? false ? $googlePlacesField['address'] : $record->formattedAddress ?? null,
                'phone' => $googlePlacesField['phone'] ?? false ? $googlePlacesField['phone'] : $record->nationalPhoneNumber ?? null,
                'website' => $googlePlacesField['website'] ?? false ? $googlePlacesField['website'] : $record->websiteUri ?? null,
                'googleUrl' => $googlePlacesField['googleUrl'] ?? false ? $googlePlacesField['googleUrl'] : $record->googleMapsLinksReviewsUri ?? null,
                'coordinates' => $googlePlacesField['coordinates'] ?? false ? $googlePlacesField['coordinates'] : ($record->locationLatitude ?? false && $record->locationLongitude ?? false ? $record->locationLatitude . ',' . $record->locationLongitude : null),
                'hours' => count($hours) ? $hours : ($record->regularOpeningHours ?? false ? json_decode($record->regularOpeningHours, true) : []),
                'hideReviews' => $googlePlacesField['hideReviews'] ?? false,
              ];
            }),
        ];
    }
}
