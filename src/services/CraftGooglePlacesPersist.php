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
  public function saveGooglePlaceData(GooglePlaceModel $model) : ?GooglePlaceRecord
  {
      try {
        if (!$model->validate()) {
            Craft::error('Google Place model did not validate: ' . $model->displayName . ' - ' . json_encode($model->getErrors()), 'craft-google-places');
            return null;
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

        $success = $record->save();

        return $success ? $record : null;
      } catch (Exception $e) {
          Craft::error('Google Place model did not persist: ' . $model->displayName . ' - ' . $e->getMessage(), 'craft-google-places');
          return null;
      }
  }

  public function findGooglePlaceData(?string $placeId, ?string $lookup) : ?GooglePlaceRecord
  {
      $record = $placeId ?? null ? GooglePlaceRecord::findOne(['placeId' => $placeId]) : null;
      $yesterday = date('Y-m-d', strtotime('-0 day'));
      if ($record == null || $record->updated == null || $yesterday > $record->updated) {
        return CraftGooglePlaces::getInstance()->googlePlacesSync->sync($placeId, $lookup);
      }

      return $record;
  }
}
