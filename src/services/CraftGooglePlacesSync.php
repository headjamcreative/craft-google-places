<?php
/**
 * Google Places Syncs plugin for Craft CMS 5.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces\services;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use Exception;
use yii\base\Component;
use headjam\craftgoogleplaces\fields\GooglePlacesSync as GooglePlacesSyncField;
use headjam\craftgoogleplaces\CraftGooglePlaces;
use headjam\craftgoogleplaces\models\GooglePlaceModel;
use headjam\craftgoogleplaces\records\GooglePlaceRecord;

/**
 * CraftGooglePlacesSync Service
 *
 * Syncs any Google Places data to the given entry.
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 */
class CraftGooglePlacesSync extends Component
{
  // Private Properties
  // =========================================================================
  private $apiDetailsMap = [
    'id',
    'displayName',
    'nationalPhoneNumber',
    'formattedAddress',
    'location',
    'googleMapsLinks',
    'websiteUri',
    'regularOpeningHours',
  ];



  // Public Methods
  // =========================================================================
  /**
   * Determine if a sync is possible, and send it to the id lookup or details query as needed
   * @param ElementInterface $element - The element that was just saved.
   * @param Field $field - The field that triggered this sync.
   * @return bool Returns true to ensure element saves.
   */
  public function sync(string $placeId, string $lookup)
  {
    if ($placeId) {
      return self::getPlaceDetails($placeId);
    } else if ($lookup) {
      return self::getPlaceId($lookup);
    } else {
      return true;
    }
  }



  // Private Methods
  // =========================================================================
  /**
   * Formats the value for the hours array.
   * @param array $hours - The opening hours as returned by the Google Places api.
   * @return array The Craft-ready array.
   */
  private function hoursFormat(array $hours): array
  {
    if (array_key_exists('weekdayDescriptions', $hours) && gettype($hours['weekdayDescriptions']) == 'array') {
      return array_map(function(string $hourRow) {
        $dayTime = explode(': ', $hourRow);
        return [
          'label' => $dayTime[0],
          'hours' => $dayTime[1]
        ];
      }, $hours['weekdayDescriptions']);
    }
    return [];
  }

  /**
   * Set the place details on the element.
   * @param array $data - The place data from the API.
   * @param Field $field - The field to set the data on.
   * @param ElementInterface $element - The element to set the data on.
   * @return bool
   */
  private function setPlaceDetails(array $data): ?GooglePlaceRecord
  {
    try {
        $data = array_filter($data, function($key) {
          return in_array($key, $this->apiDetailsMap);
        }, ARRAY_FILTER_USE_KEY);

        $model = new GooglePlaceModel();
        $model->placeId = $data['id'];
        $model->displayName = $data['displayName']['text'];
        $model->nationalPhoneNumber = $data['nationalPhoneNumber'] ?? null;
        $model->formattedAddress = $data['formattedAddress'] ?? null;
        $model->locationLatitude = $data['location'] ?? null ? (float)$data['location']['latitude'] ?? null : null;
        $model->locationLongitude = $data['location'] ?? null ? (float)$data['location']['longitude'] ?? null : null;
        $model->googleMapsLinksReviewsUri = $data['googleMapsLinks'] ?? null ? $data['googleMapsLinks']['reviewsUri'] ?? null : null;
        $model->websiteUri = $data['websiteUri'] ?? null;
        $model->regularOpeningHours = $data['regularOpeningHours'] ?? null ? self::hoursFormat($data['regularOpeningHours'] ?? null) : null;

        return CraftGooglePlaces::getInstance()->googlePlacesPersist->saveGooglePlaceData($model);
    } catch (Exception $error) {
        Craft::error('Error setting place details: ' . $data['displayName']['text'] . ' - ' . $error->getMessage(), 'craft-google-places');
        return null;
    }
  }

  /**
   * Query the details for a given GooglePlace and save it against the value.
   * Returns true regardless of outcome so the entry saves successfully.
   * @param string $placeId - The place ID to lookup.
   * @return bool Returns true.
   */
  private function getPlaceDetails(string $placeId)
  {
    try {
      if ($placeId) {
        $result = CraftGooglePlaces::getInstance()->googlePlacesApi->placeDetails($placeId);
        if (isset($result['success']) && isset($result['data'])) {
          return self::setPlaceDetails($result['data']);
        }
      }

      return true;
    } catch (Exception $error) {
      Craft::error('Error getting place details: ' . $error->getMessage(), 'craft-google-places');
      return true;
    }
  }

  /**
   * Lookup a GooglePlaces place id for a given query and save it against the value.
   * Returns true regardless of outcome so the entry saves successfully.
   * @param string $lookup - The text to lookup in Google Places API.
   * @return bool Returns true.
   */
  private function getPlaceId(string $lookup)
  {
    try {
      // Just being extra-safe with another check
      if ($lookup) {
        $result = CraftGooglePlaces::getInstance()->googlePlacesApi->placeSearch($lookup);
        if (
          $result['success'] &&
          $result['data']['places'][0]['id'] ?? false
        ) {
          return self::setPlaceDetails($result['data']['places'][0]);
        }
      }

      return true;
    } catch (Exception $error) {
      return true;
    }
  }
}
