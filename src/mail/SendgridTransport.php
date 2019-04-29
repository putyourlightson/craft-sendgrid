<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use Swift_Mime_SimpleMessage;

class SendgridTransport extends Transport
{
    // Constants
    // =========================================================================

    /**
     * @const int
     */
    const SUCCESS_STATUS_CODE = 202;

    // Properties
    // =========================================================================

    /**
     * @var SendGrid
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
        try {
            $email = $this->_createMessage($message);
        }
        catch (TypeException $e) {
            return 0;
        }

        try {
            $response = $this->_client->send($email);

            if ($response->statusCode() != self::SUCCESS_STATUS_CODE) {
                return 0;
            }
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
     * @throws TypeException
     */
    private function _createMessage(Swift_Mime_SimpleMessage $message): Mail
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

        // Add content based on message's content types
        $contentTypes = ['text/plain', 'text/html'];

        foreach ($message->getChildren() as $mimeEntity) {
            if (in_array($mimeEntity->getBodyContentType(), $contentTypes)) {
                $email->addContent($mimeEntity->getBodyContentType(), $mimeEntity->getBody());
            }
        }

        return $email;
    }

}
