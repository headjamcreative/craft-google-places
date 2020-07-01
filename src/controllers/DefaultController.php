<?php
/**
 * Google Places Syncs plugin for Craft CMS 3.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces\controllers;

use headjam\craftgoogleplaces\CraftGooglePlaces;

use Craft;
use craft\services\Fields;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Gets any entries with a Google Place Sync field and syncs the data.
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 */
class DefaultController extends Controller
{

  // Protected Properties
  // =========================================================================
  /**
   * @var bool|array Allows anonymous access to this controller's actions. The actions must be in 'kebab-case'
   * @access protected
   */
  protected $allowAnonymous = ['index'];


    
  // Public Methods
  // =========================================================================
  /**
   * Get all entries with a GooglePlacesSync field
   *
   * @return mixed
   */
  public function actionIndex()
  {
    $entries = CraftGooglePlaces::getInstance()->googlePlacesSync->syncAll();
    return $this->asJson($entries);
  }
}
