<?php
/**
 * Google Places Syncs plugin for Craft CMS 5.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces\controllers;

use Craft;
use craft\helpers\Queue;
use craft\web\Controller;
use yii\web\Response;
use headjam\craftgoogleplaces\CraftGooglePlaces;
use headjam\craftgoogleplaces\jobs\SyncAllPlaces;

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
    public $defaultAction = 'index';

    // Protected Properties
    // =========================================================================
    /**
     * @var bool|array Allows anonymous access to this controller's actions. The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['index'];



    // Public Methods
    // =========================================================================
    /**
     * Get all entries with a GooglePlacesSync field
     *
     * @return mixed
     */
    public function actionIndex(): Response
    {
      try {
        Queue::push(new SyncAllPlaces());
        return $this->asJson(['success' => true]);
      } catch (\Throwable $e) {
        return $this->asJson(['success' => false]);
      }
    }
}
