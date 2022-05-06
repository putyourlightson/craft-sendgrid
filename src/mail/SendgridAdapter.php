<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Craft;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\mail\transportadapters\BaseTransportAdapter;
use SendGrid;

/**
 *
 * @property mixed $settingsHtml
 */
class SendgridAdapter extends BaseTransportAdapter
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'SendGrid';
    }

    // Properties
    // =========================================================================

    /**
     * @var string The API key
     */
    public $apiKey;

    /**
     * @var string[] The email categories
     */
    public $categories;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'apiKey' => Craft::t('sendgrid', 'API Key'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['apiKey'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['apiKey'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sendgrid/_settings', [
            'adapter' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function defineTransport()
    {
        // Create new client
        $client = new SendGrid(Craft::parseEnv($this->apiKey));

        $categories = [];

        // Flatten categories array
        if (is_array($this->categories)) {
            $categories = array_map(function ($value) {
                return $value[0];
            }, $this->categories);
        }

        return new SendgridTransport($client, $categories);
    }
}
