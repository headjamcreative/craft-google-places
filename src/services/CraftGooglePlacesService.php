<?php
/**
 * Craft Google Places plugin for Craft CMS 3.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces\services;

use headjam\craftgoogleplaces\CraftGooglePlaces;

use Craft;
use craft\base\Component;

/**
 * CraftGooglePlacesService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 */
class CraftGooglePlacesService extends Component
{
  // Public Methods
  // =========================================================================
  /**
   * This function can literally be anything you want, and you can have as many service
   * functions as you want
   *
   * From any other plugin file, call it like this:
   *
   *     CraftGooglePlaces::$plugin->craftGooglePlacesService->exampleService()
   *
   * @return mixed
   */
  public function exampleService()
  {
    $result = 'something';
    // Check our Plugin's settings for `someAttribute`
    if (CraftGooglePlaces::$plugin->getSettings()->someAttribute) {
    }

    return $result;
  }
}
