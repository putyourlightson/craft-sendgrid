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
     * @var string The email category
     */
    public $emailCategory;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'apiKey' => Craft::t('sendgrid', 'API Key'),
            'emailCategory' => Craft::t('sendgrid','Email category')
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
                'attributes' => ['apiKey','emailCategory'],
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
     * @return array
     */
    public function getEmailCategories(){

        $categories = Craft::parseEnv($this->emailCategory);

        if($categories){
            return explode(',',str_replace(' ','',$categories));
        }

        return null;

    }

    /**
     * @inheritdoc
     */
    public function defineTransport()
    {
        // Create new client
        $client = new SendGrid(Craft::parseEnv($this->apiKey));

        return new SendgridTransport($client, $this->getEmailCategories());
    }
}
