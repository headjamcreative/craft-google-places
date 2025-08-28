<?php
/**
 * Google Places Syncs plugin for Craft CMS 5.x
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use headjam\craftgoogleplaces\fields\GooglePlacesSync;
use headjam\craftgoogleplaces\models\Settings;
use headjam\craftgoogleplaces\services\CraftGooglePlacesApi;
use headjam\craftgoogleplaces\services\CraftGooglePlacesSync;
use yii\base\Event;
use Psr\Log\LogLevel;
use craft\log\MonologTarget;
use Monolog\Formatter\LineFormatter;
use yii\log\Logger;

/**
 * Google Places Sync plugin
 *
 * @method static CraftGooglePlaces getInstance()
 * @method Settings getSettings()
 * @author Headjam
 * @copyright Headjam
 * @license MIT
 * @property-read CraftGooglePlacesApi $craftGooglePlacesApi
 * @property-read CraftGooglePlacesSync $craftGooglePlacesSync
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
    public string $schemaVersion = '2.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = false;


    // Public Methods
    // =========================================================================
    /**
     * Configure the services.
     */
    public static function config(): array
    {
        return [
            'components' => ['googlePlacesApi' => CraftGooglePlacesApi::class, 'googlePlacesSync' => CraftGooglePlacesSync::class],
        ];
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
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Create a new file target for custom logging
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'craft-google-places',
            'categories' => ['craft-google-places'],
            'level' => LogLevel::ERROR,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "%datetime% %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);

        $this->attachEventHandlers();
    }

    // Protected Methods
    // =========================================================================
    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('craft-google-places/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = GooglePlacesSync::class;
        });
    }
}
