<?php
/**
 * Google Places Persists plugin for Craft CMS 5.x
 *
 * Persists Google Places data to the database.
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
use yii\log\Logger;

/**
 * CraftGooglePlacesPersist Service
 *
 * Persists any Google Places data to the database.
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     2.0.1
 */
class CraftGooglePlacesPersist extends Component {
  /**
   * Save Google Place data to the database.
   *
   * @param GooglePlaceModel $model
   * @return bool
   */
  public function saveGooglePlaceData(GooglePlaceModel $model) : bool
  {
      try {
        if (!$model->validate()) {
            Craft::error('Google Place model did not validate: ' . $model->displayName . ' - ' . json_encode($model->getErrors()), 'craft-google-places');
            return false;
        }

        $record = GooglePlaceRecord::findOne(['placeId' => $model->placeId]);
        if ($record == null) {
          $record = new GooglePlaceRecord();
        }

        $record->placeId = $model->placeId;
        $record->displayName = $model->displayName;
        $record->nationalPhoneNumber = $model->nationalPhoneNumber;
        $record->formattedAddress = $model->formattedAddress;
        $record->locationLatitude = $model->locationLatitude;
        $record->locationLongitude = $model->locationLongitude;
        $record->googleMapsLinksReviewsUri = $model->googleMapsLinksReviewsUri;
        $record->websiteUri = $model->websiteUri;
        $record->regularOpeningHours = $model->regularOpeningHours;
        $record->updated = $model->updated ?? date('Y-m-d H:i:s');

        return $record->save();
      } catch (Exception $e) {
          Craft::error('Google Place model did not persist: ' . $model->displayName . ' - ' . $e->getMessage(), 'craft-google-places');
          return false;
      }
  }
}
