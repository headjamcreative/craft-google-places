<?php
/**
 * Google Places Syncs plugin for Craft CMS 5.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Headjam
 */

namespace headjam\craftgoogleplaces\services;

use Craft;
use Exception;
use GuzzleHttp\Utils;
use yii\base\Component;
use headjam\craftgoogleplaces\CraftGooglePlaces;

/**
 * CraftGooglePlacesApi Service
 *
 * Queries the Google Places api for data.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 */
class CraftGooglePlacesApi extends Component
{
  // Static Properties
  // =========================================================================
  private static array $fields = [
    'id',
    'displayName',
    'nationalPhoneNumber',
    'formattedAddress',
    'location.latitude',
    'location.longitude',
    'googleMapsLinks.reviewsUri',
    'websiteUri',
    'regularOpeningHours',
  ];

  private function placesFields(): array {
    return array_map(fn($field) => "places.$field", self::$fields);
  }

  // Public Methods
  // =========================================================================
  /**
   * Lookup a business in Google Places via either a phone number, business name, or address.
   * @return array An array containing a status and either error or data properties.
   */
  public function placeSearch(string $input): mixed
  {
    $response = $this->googleApiRequest(
      ':searchText',
      'POST',
      self::placesFields(),
      ['body' => json_encode(['textQuery' => $input])],
    );

    return $response;
  }

  /**
   * Get all the details of a place based on it's Google-assigned place_id.
   * @param string $placeId - The Google place_id to query by.
   * @return array An array containing a status and either error or data properties.
   */
  public function placeDetails(string $placeId)
  {
    $response = $this->googleApiRequest('/' . urlencode($placeId), 'GET', self::$fields);
    return $response;
  }



  // Private Methods
  // =========================================================================
  /**
   * Format a Google Maps api request.
   * @param string $endpoint - The endpoint to query.
   * @param array $params - The query string items to append to the request.
   * @return array An array containing a status and either error or data properties.
   */
  private function googleApiRequest(string $endpoint, string $method, array $fields = [], array $params = [])
  {
    try {
      $key = CraftGooglePlaces::getInstance()->getSettings()->googleApiKey;
      if (isset($key) && $key !== '') {
        $client = new \GuzzleHttp\Client();
        $url = 'https://places.googleapis.com/v1/places' . $endpoint . '?fields=' . implode(',', $fields) . '&key=' . $key;
        $response = $client->request($method, $url, $params);
        if ($response->getStatusCode() === 200) {
          $body = $response->getBody()->getContents();
          $data = Utils::jsonDecode($body, true);
          return [
            'success' => true,
            'data' => $data
          ];
        }
      }
      return [
        'status' => false,
        'error' => 'Server Error'
      ];
    } catch (Exception $error) {
      return [
        'success' => false,
        'error' => $error->getMessage()
      ];
    }
  }
}
