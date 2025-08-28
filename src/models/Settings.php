<?php

namespace headjam\craftgoogleplaces\models;

use Craft;
use craft\base\Model;

/**
 * Google Places Sync settings
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================
    /**
     * Some field model attribute
     * @var string
     */
    public $googleApiKey = '';

    // Public Methods
    // =========================================================================
    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
      return [
        ['googleApiKey', 'string'],
        ['googleApiKey', 'required'],
        ['googleApiKey', 'default', 'value' => ''],
      ];
    }
}
