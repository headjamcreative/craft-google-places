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
use yii\log\Logger;

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
    'id' => [
      'key' => 'id',
      'format' => 'simple'
    ],
    'displayName' => [
      'key' => 'name',
      'format' => 'nameFormat'
    ],
    'nationalPhoneNumber' => [
      'key' => 'phone',
      'format' => 'simple'
    ],
    'formattedAddress' => [
      'key' => 'address',
      'format' => 'simple'
    ],
    'location' => [
      'key' => 'coordinates',
      'format' => 'coordsFormat'
    ],
    'googleMapsLinks' => [
      'key' => 'googleUrl',
      'format' => 'googleUrlFormat'
    ],
    'websiteUri' => [
      'key' => 'website',
      'format' => 'simple'
    ],
    'regularOpeningHours' => [
      'key' => 'hours',
      'format' => 'hoursFormat'
    ]
  ];



  // Public Methods
  // =========================================================================
  /**
   * Determine if a sync is possible, and send it to the id lookup or details query as needed
   * @param ElementInterface $element - The element that was just saved.
   * @param Field $field - The field that triggered this sync.
   * @return bool Returns true to ensure element saves.
   */
  public function sync(ElementInterface $element, Field $field)
  {
    $value = $element->getFieldValue($field->handle);
    $value['updated'] = time();
    if (isset($value['id']) && $value['id'] !== '') {
      return self::getPlaceDetails($value, $field, $element);
    } else if (isset($value['lookup']) && $value['lookup'] !== '') {
      return self::getPlaceId($value, $field, $element);
    } else {
      return true;
    }
  }



  // Private Methods
  // =========================================================================
  /**
   * Formats the value for the name.
   * @param array $name - The name as returned by the Google Places api.
   * @return string The Craft-ready name.
   */
  private function nameFormat(array $name)
  {
    return $name['text'] ?? '';
  }

  /**
   * Formats the Google URL.
   * @param array $url - The URL as returned by the Google Places api.
   * @return string The Craft-ready URL.
   */
  private function googleUrlFormat(array $url)
  {
    return $url['reviewsUri'] ?? '';
  }

  /**
   * Formats the value for the location coordinates.
   * @param array $coords - The geometry as returned by the Google Places api.
   * @param array The Craft-ready array.
   */
  private function coordsFormat(array $coords) {
    if (isset($coords['latitude']) && isset($coords['longitude'])) {
      return $coords['latitude'] . ',' . $coords['longitude'];
    }

    return '';
  }

  /**
   * Formats the value for the hours array.
   * @param array $hours - The opening hours as returned by the Google Places api.
   * @param array The Craft-ready array.
   */
  private function hoursFormat(array $hours)
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
  private function setPlaceDetails(array $data, Field $field, ElementInterface $element): bool
  {
    $data = array_filter($data, function($key) {
      return array_key_exists($key, $this->apiDetailsMap);
    }, ARRAY_FILTER_USE_KEY);

    foreach ($data as $key => $val) {
      $format = $this->apiDetailsMap[$key]['format'];
      if ($format == 'simple') {
        $value[$this->apiDetailsMap[$key]['key']] = $val;
      } else {
        $value[$this->apiDetailsMap[$key]['key']] = $this->$format($val);
      }
    }
    $value["updated"] = date('Y-m-d H:i:s');

    $element->setFieldValue($field->handle, $value);

    return true;
  }

  /**
   * Query the details for a given GooglePlace and save it against the value.
   * Returns true regardless of outcome so the entry saves successfully.
   * @param array $value - The existing value for the field.
   * @param Field $field - The field that triggered this action.
   * @param ElementInterface $element - The element the field belongs to.
   * @return bool Returns true.
   */
  private function getPlaceDetails(array $value, Field $field, ElementInterface $element)
  {
    try {
      $id = $value['id'];
      if (isset($id) && $id !== '') {
        $result = CraftGooglePlaces::getInstance()->googlePlacesApi->placeDetails($id);
        if (isset($result['success']) && isset($result['data'])) {
          return self::setPlaceDetails($result['data'], $field, $element);
        }
      }

      return true;
    } catch (Exception $error) {
      return true;
    }
  }

  /**
   * Lookup a GooglePlaces place id for a given query and save it against the value.
   * Returns true regardless of outcome so the entry saves successfully.
   * @param array $value - The existing value for the field.
   * @param Field $field - The field that triggered this action.
   * @param ElementInterface $element - The element the field belongs to.
   * @return bool Returns true.
   */
  private function getPlaceId(array $value, Field $field, ElementInterface $element)
  {
    try {
      $lookup = $value['lookup'];
      // Just being extra-safe with another check
      if (isset($lookup) && $lookup !== '') {
        $result = CraftGooglePlaces::getInstance()->googlePlacesApi->placeSearch($lookup);
        if (
          $result['success'] &&
          $result['data']['places'][0]['id'] ?? false
        ) {
          return self::setPlaceDetails($result['data']['places'][0], $field, $element);
        }
      }

      return true;
    } catch (Exception $error) {
      return true;
    }
  }
}
