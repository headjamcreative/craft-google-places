<?php

namespace headjam\craftgoogleplaces\models;

use Craft;
use craft\base\Model;

/**
 * Google Place model.
 * @author    Ben Norman
 * @package   CraftGooglePlaces
 * @since     2.0.1
 */
class GooglePlaceModel extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var string|null
     */
    public ?string $placeId = null;

    /**
     * @var string|null
     */
    public ?string $displayName = null;

    /**
     * @var string|null
     */
    public ?string $nationalPhoneNumber = null;

    /**
     * @var string|null
     */
    public ?string $formattedAddress = null;

    /**
     * @var float|null
     */
    public ?float $locationLatitude = null;

    /**
     * @var float|null
     */
    public ?float $locationLongitude = null;

    /**
     * @var string|null
     */
    public ?string $googleMapsLinksReviewsUri = null;

    /**
     * @var string|null
     */
    public ?string $websiteUri = null;

    /**
     * @var array|null
     */
    public ?array $regularOpeningHours = null;

    /**
     * @var string|null
     */
    public ?string $updated = null;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['placeId', 'displayName'], 'required'],
            [['locationLatitude', 'locationLongitude'], 'number'],
            [['regularOpeningHours'], 'safe'],
        ];
    }
}
