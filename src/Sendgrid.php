<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\MailerHelper;
use putyourlightson\sendgrid\mail\SendgridAdapter;
use yii\base\Event;

class Sendgrid extends Plugin
{
    /**
     * @var Sendgrid
     */
    public static Sendgrid $plugin;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Event::on(MailerHelper::class, MailerHelper::EVENT_REGISTER_MAILER_TRANSPORTS,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SendgridAdapter::class;
            }
        );
    }
}
