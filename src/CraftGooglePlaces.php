<?php
/**
 * Craft Google Places plugin for Craft CMS 3.x
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces;

use headjam\craftgoogleplaces\services\CraftGooglePlacesApi as CraftGooglePlacesApiService;
use headjam\craftgoogleplaces\models\Settings;
use headjam\craftgoogleplaces\fields\GooglePlacesSync as GooglePlacesSyncField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 *
 * @property  CraftGooglePlacesApiService $craftGooglePlacesService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class CraftGooglePlaces extends Plugin
{
  // Static Properties
  // =========================================================================
  /**
   * Static property that is an instance of this plugin class so that it can be accessed via
   * CraftGooglePlaces::$plugin
   *
   * @var CraftGooglePlaces
   */
  public static $plugin;

  // Public Properties
  // =========================================================================
  /**
   * To execute your plugin’s migrations, you’ll need to increase its schema version.
   *
   * @var string
   */
  public $schemaVersion = '1.0.0';

  /**
   * Set to `true` if the plugin should have a settings view in the control panel.
   *
   * @var bool
   */
  public $hasCpSettings = true;

  /**
   * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
   *
   * @var bool
   */
  public $hasCpSection = false;



  // Public Methods
  // =========================================================================
  /**
   * A customer logger for the plugin.
   */
  public static function log($message){
    Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'craft-google-places');
  }

  /**
   * Set our $plugin static property to this class so that it can be accessed via
   * CraftGooglePlaces::$plugin
   *
   * Called after the plugin class is instantiated; do any one-time initialization
   * here such as hooks and events.
   *
   * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
   * you do not need to load it in your init() method.
   *
   */
  public function init()
  {
    parent::init();
    self::$plugin = $this;
    $this->setComponents([
      'googlePlacesApiService' => CraftGooglePlacesApiService::class
    ]);

    // Init the customer logger
    $fileTarget = new \craft\log\FileTarget([
      'logFile' => Craft::getAlias('@storage/logs/craftGooglePlaces.log'),
      'categories' => ['craft-google-places']
    ]);
    Craft::getLogger()->dispatcher->targets[] = $fileTarget;

    // Register our site routes
    Event::on(
      UrlManager::class,
      UrlManager::EVENT_REGISTER_SITE_URL_RULES,
      function (RegisterUrlRulesEvent $event) {
        $event->rules['siteActionTrigger1'] = 'craft-google-places/default';
      }
    );

    // Register our CP routes
    Event::on(
      UrlManager::class,
      UrlManager::EVENT_REGISTER_CP_URL_RULES,
      function (RegisterUrlRulesEvent $event) {
        $event->rules['cpActionTrigger1'] = 'craft-google-places/default/do-something';
      }
    );

    // Register our fields
    Event::on(
      Fields::class,
      Fields::EVENT_REGISTER_FIELD_TYPES,
      function (RegisterComponentTypesEvent $event) {
        $event->types[] = GooglePlacesSyncField::class;
      }
    );

    // Do something after we're installed
    Event::on(
      Plugins::class,
      Plugins::EVENT_AFTER_INSTALL_PLUGIN,
      function (PluginEvent $event) {
        if ($event->plugin === $this) {
          // We were just installed
        }
      }
    );
  }



  // Protected Methods
  // =========================================================================
  /**
   * Creates and returns the model used to store the plugin’s settings.
   *
   * @return \craft\base\Model|null
   */
  protected function createSettingsModel()
  {
    return new Settings();
  }

  /**
   * Returns the rendered settings HTML, which will be inserted into the content
   * block on the settings page.
   *
   * @return string The rendered settings HTML
   */
  protected function settingsHtml(): string
  {
    $lookup = CraftGooglePlaces::getInstance()->googlePlacesApiService->placeSearch('+61249291154');
    CraftGooglePlaces::log(json_encode($lookup));
    if ($lookup['success'] && $lookup['data']['candidates'][0]) {
      $details = CraftGooglePlaces::getInstance()->googlePlacesApiService->placeDetails($lookup['data']['candidates'][0]['place_id']);
      CraftGooglePlaces::log(json_encode($details));
    }
    return Craft::$app->view->renderTemplate(
      'craft-google-places/settings',
      [
        'settings' => $this->getSettings()
      ]
    );
  }
}
