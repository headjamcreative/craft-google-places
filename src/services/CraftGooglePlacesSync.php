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
use craft\base\ElementInterface;
use craft\base\Field;

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
    'formatted_address' => [
      'key' => 'address',
      'format' => 'simple'
    ],
    'formatted_phone_number' => [
      'key' => 'phone',
      'format' => 'simple'
    ],
    'website' => [
      'key' => 'website',
      'format' => 'simple'
    ],
    'url' => [
      'key' => 'googleUrl',
      'format' => 'simple'
    ],
    'opening_hours' => [
      'key' => 'hours',
      'format' => 'hoursFormat',
    ],
    'geometry' => [
      'key' => 'coordinates',
      'format' => 'coordsFormat'
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
    if (isset($value['id']) && $value['id'] !== '') {
      return $this->getPlaceDetails($value, $field, $element);
    } else if (isset($value['lookup']) && $value['lookup'] !== '') {
      return $this->getPlaceId($value, $field, $element);
    } else {
      return true;
    }
  }



  // Private Methods
  // =========================================================================
  /** 
   * Formats the value for the hours array.
   * @param array $hours - The opening hours as returned by the Google Places api.
   * @param array The Craft-ready array.
   */
  private function hoursFormat(array $hours)
  {
    if (array_key_exists('weekday_text', $hours) && gettype($hours['weekday_text'] == 'array')) {
      return array_map(function(string $hourRow) {
        $dayTime = explode(': ', $hourRow);
        return [
          'label' => $dayTime[0],
          'hours' => $dayTime[1]
        ];
      }, $hours['weekday_text']);
    }
    return [];
  }

  /**
   * Formats the value for the location coordinates.
   * @param array $coords - The geometry as returned by the Google Places api.
   * @param array The Craft-ready array.
   */
  private function coordsFormat(array $coords) {
   if (isset($coords['location']) && isset($coords['location']['lat']) && isset($coords['location']['lng'])) {
    return $coords['location']['lat'] . ',' . $coords['location']['lng'];
   } 
   return '';
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
      // Just being extra-safe with another check
      if (isset($id) && $id !== '') {
        $result = CraftGooglePlaces::getInstance()->googlePlacesApi->placeDetails($id);
        if ($result['success'] && $result['data'] && $result['data']['result']) {
          $data = array_filter($result['data']['result'], function($key) {
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
          $element->setFieldValue($field->handle, $value);
        }
      }
      return true;
    } catch (Exception $error) {
      // We'll continue to save anyway
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
          $result['data']['candidates'] && 
          $result['data']['candidates'][0] && 
          $result['data']['candidates'][0]['place_id']
        ) {
          $value['id'] = $result['data']['candidates'][0]['place_id'];
          $element->setFieldValue($field->handle, $value);
          return $this->getPlaceDetails($value, $field, $element);
        }
      }
      return true;
    } catch (Exception $error) {
      // We'll continue to save anyway
      return true;
    }
  }
}