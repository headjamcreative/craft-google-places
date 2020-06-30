<?php
/**
 * Craft Google Places plugin for Craft CMS 3.x
 *
 * Syncs Google Places API data to entries.
 *
 * @link      https://www.headjam.com.au
 * @copyright Copyright (c) 2020 Ben Norman
 */

namespace headjam\craftgoogleplaces\fields;

use headjam\craftgoogleplaces\CraftGooglePlaces;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * GooglePlacesSync Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     1.0.0
 */
class GooglePlacesSync extends Field
{
  /**
   * Performs actions after the element has been saved.
   *
   * @param ElementInterface $element The element that was just saved
   * @param bool $isNew Whether the element is brand new
   * @return bool True if the element should continue to save.
   */
  public function beforeElementSave(ElementInterface $element, bool $isNew): bool
  {
    if (
      !$element->getIsRevision() &&
      $element->isFieldDirty($this->handle)
    ) {
      return CraftGooglePlaces::getInstance()->googlePlacesSync->sync($element, $this);
    }
    return true;
  }

  // Static Methods
  // =========================================================================
  /**
   * Returns the display name of this class.
   *
   * @return string The display name of this class.
   */
  public static function displayName(): string
  {
    return Craft::t('craft-google-places', 'Google Places Sync');
  }

  /**
   * Returns the column type that this field should get within the content table.
   * This method will only be called if [[hasContentColumn()]] returns true.
   *
   * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
   * to convert the give column type to the physical one. For example, `string` will be converted
   * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
   * appended as well.
   * @see \yii\db\QueryBuilder::getColumnType()
   */
  public function getContentColumnType(): string
  {
    return Schema::TYPE_TEXT;
  }

  /**
   * Normalizes the field’s value for use.
   * This method is called when the field’s value is first accessed from the element. For example, the first time
   * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
   * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
   * [[serializeValue()]]’s $value arguments will be set to.
   *
   * @param mixed                 $value   The raw field value
   * @param ElementInterface|null $element The element the field is associated with, if there is one
   *
   * @return mixed The prepared field value
   */
  public function normalizeValue($value, ElementInterface $element = null)
  {
    if ($value === null) {
      $value = [];
    }
    return Json::decodeIfJson($value);
  }

  /**
   * Prepares the field’s value to be stored somewhere, like the content table or JSON-encoded in an entry revision table.
   *
   * Data types that are JSON-encodable are safe (arrays, integers, strings, booleans, etc).
   * Whatever this returns should be something [[normalizeValue()]] can handle.
   *
   * @param mixed $value The raw field value
   * @param ElementInterface|null $element The element the field is associated with, if there is one
   * @return mixed The serialized field value
   */
  public function serializeValue($value, ElementInterface $element = null)
  {
    return parent::serializeValue(Json::encode($value), $element);
  }

  /**
   * Returns the field’s input HTML.
   *
   * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
   *                                               raw POST data (i.e. if there was a validation error), or null
   * @param ElementInterface|null $element         The element the field is associated with, if there is one
   *
   * @return string The input HTML.
   */
  public function getInputHtml($value, ElementInterface $element = null): string
  {
    // Get our id and namespace
    $id = Craft::$app->getView()->formatInputId($this->handle);
    $namespacedId = Craft::$app->getView()->namespaceInputId($id);

    // Variables to pass down to our field JavaScript to let it namespace properly
    $jsonVars = [
      'id' => $id,
      'name' => $this->handle,
      'namespace' => $namespacedId,
      'prefix' => Craft::$app->getView()->namespaceInputId(''),
    ];
    $jsonVars = Json::encode($jsonVars);

    // Render the input template
    return Craft::$app->getView()->renderTemplate(
      'craft-google-places/_components/fields/GooglePlacesSync_input',
      [
        'name' => $this->handle,
        'value' => $value,
        'field' => $this,
        'id' => $id,
        'namespacedId' => $namespacedId,
      ]
    );
  }
}
