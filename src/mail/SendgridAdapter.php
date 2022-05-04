<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Craft;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;
use craft\mail\transportadapters\BaseTransportAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * @property-read null|string $settingsHtml
 */
class SendgridAdapter extends BaseTransportAdapter
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'SendGrid';
    }

    /**
     * @var string The API key
     */
    public string $apiKey = '';

    /**
     * @var string[] The email categories
     */
    public array $categories = [];

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
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('sendgrid/_settings', [
            'adapter' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function defineTransport(): array|AbstractTransport
    {
        $apiKey = App::parseEnv($this->apiKey);
        $headers = [];

        foreach ($this->categories as $category) {
            $headers[] = new TagHeader($category);
        }

        $client = HttpClient::create([
            'headers' => $headers,
        ]);

        return new SendgridApiTransport($apiKey, $client);
    }

    /**
     * @inheritdoc
     */
    protected function defineBehaviors(): array
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
    protected function defineRules(): array
    {
        return [
            [['apiKey'], 'required'],
        ];
    }
}
