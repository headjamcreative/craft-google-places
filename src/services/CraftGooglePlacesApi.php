<?php
/**
 * Google Places Syncs plugin for Craft CMS 3.x
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
  // Public Methods
  // =========================================================================
  /**
   * Lookup a business in Google Places via either a phone number, business name, or address.
   * @return array An array containing a status and either error or data properties.
   */
  public function placeSearch(string $input)
  {
    $type = $input[0] == '+' && strlen($input) >= 11 ? 'phonenumber' : 'textquery';
    $params = '&inputtype=' . $type . '&input=' . urlencode($input);
    return $this->googleApiRequest('place/findplacefromtext', $params);
  }

  /** 
   * Get all the details of a place based on it's Google-assigned place_id.
   * @param string $placeId - The Google place_id to query by.
   * @return array An array containing a status and either error or data properties.
   */
  public function placeDetails(string $placeId)
  {
    return $this->googleApiRequest('place/details', '&place_id=' . urlencode($placeId));
  }



  // Private Methods
  // =========================================================================
  /** 
   * Format a Google Maps api request.
   * @param string $endpoint - The endpoint to query.
   * @param string $params - The query string items to append to the request.
   * @return array An array containing a status and either error or data properties.
   */
  private function googleApiRequest(string $endpoint, string $params)
  {
    try {
      $key = CraftGooglePlaces::getInstance()->getSettings()->googleApiKey;
      if (isset($key) && $key !== '') {
        $client = new \GuzzleHttp\Client();
        $url = 'https://maps.googleapis.com/maps/api/' . $endpoint . '/json?key=' . $key . $params;
        $response = $client->request('GET', $url);
        if ($response->getStatusCode() === 200) {
          $body = $response->getBody()->getContents();
          $data = \GuzzleHttp\json_decode($body, true);
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