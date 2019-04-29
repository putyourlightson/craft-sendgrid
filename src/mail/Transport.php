<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Swift_Events_EventListener;
use Swift_Transport;

abstract class Transport implements Swift_Transport
{
    /**
     * @inheritdoc
     */
    public function isStarted(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function start(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function stop(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function registerPlugin(Swift_Events_EventListener $plugin) { }
}
