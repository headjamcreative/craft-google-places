<?php

namespace headjam\craftgoogleplaces\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Google Place record.
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     2.0.1
 *
 */
class GooglePlaceRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%craftgoogleplaces_googleplaces}}';
    }
}
