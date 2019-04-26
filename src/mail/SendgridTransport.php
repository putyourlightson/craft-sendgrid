<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use Swift_Mime_SimpleMessage;

class SendgridTransport extends Transport
{
    // Properties
    // =========================================================================

    /**
     * @var SesClient
     */
    private $_client;

    // Public Methods
    // =========================================================================

    /**
     * Constructor
     *
     * @param SendGrid $client
     */
    public function __construct(SendGrid $client)
    {
        $this->_client = $client;
    }

    /**
     * @inheritdoc
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $email = $this->_formatMessage($message);

        try {
            $this->_client->send($email);
        }
        catch (Exception $e) {
            return 0;
        }

        return count($message->getTo());
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return Mail
     */
    private function _formatMessage(Swift_Mime_SimpleMessage $message): Mail
    {
        $fromEmail = key($message->getFrom());
        $fromName = $message->getFrom()[$fromEmail];

        $email = new Mail();
        $email->setFrom($fromEmail, $fromName);
        $email->setReplyTo($message->getReplyTo());
        $email->setSubject($message->getSubject());
        $email->addTos($message->getTo() ?? []);
        $email->addCcs($message->getCc() ?? []);
        $email->addBccs($message->getBcc() ?? []);
        $email->addContent('text/html', $message->toString());
        $email->addContent('text/plain', $message->toString());

        return $email;
    }

}
