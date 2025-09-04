<?php

namespace headjam\craftgoogleplaces\web\twig;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
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
            new TwigFunction('syncPlacesData', function(mixed $entry) {
              Craft::$app->elements->saveElement($entry);
            }),
        ];
    }
}
