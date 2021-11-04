<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Craft;
use Exception;
use SendGrid;
use SendGrid\Mail\Attachment;
use SendGrid\Mail\EmailAddress;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use Swift_Mime_Attachment;
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
            $email = $this->_getEmail($message);
        }
        catch (TypeException $e) {
            Craft::warning($e->getMessage(), 'sendgrid');

            return 0;
        }

        try {
            $response = $this->_client->send($email);

            if ($response->statusCode() != self::SUCCESS_STATUS_CODE) {
                Craft::warning($response->body(), 'sendgrid');

                return 0;
            }
        }
        catch (Exception $e) {
            Craft::warning($e->getMessage(), 'sendgrid');

            return 0;
        }

        return count($message->getTo());
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return Mail
     * @throws TypeException
     */
    private function _getEmail(Swift_Mime_SimpleMessage $message): Mail
    {
        $from = $this->_getEmailAddress($message->getFrom());
        $replyTo = $this->_getEmailAddress($message->getReplyTo());

        $email = new Mail();
        $email->setFrom($from->getEmail(), $from->getName());

        if ($replyTo !== null) {
            $email->setReplyTo($replyTo->getEmail(), $replyTo->getName());
        }

        $email->setSubject($message->getSubject());

        if (!empty($message->getTo())) {
            $email->addTos($message->getTo());
        }

        if (!empty($message->getCc())) {
            $email->addCcs($message->getCc());
        }

        if (!empty($message->getBcc())) {
            $email->addBccs($message->getBcc());
        }

        // Add content based on message's content types
        $contentTypes = ['text/plain', 'text/html'];

        foreach ($message->getChildren() as $mimeEntity) {
            if ($mimeEntity instanceof Swift_Mime_Attachment) {
                $attachment = new Attachment();
                $attachment->setContent(base64_encode($mimeEntity->getBody()));
                $attachment->setType($mimeEntity->getContentType());
                $attachment->setFilename($mimeEntity->getFilename());
                $attachment->setDisposition($mimeEntity->getDisposition());
                $attachment->setContentId($mimeEntity->getId());

                $email->addAttachment($attachment);
            }
            elseif (in_array($mimeEntity->getBodyContentType(), $contentTypes)) {
                $email->addContent($mimeEntity->getBodyContentType(), $mimeEntity->getBody());
            }
        }

        // If email content is empty then set it to the message body
        // (resolves https://github.com/putyourlightson/craft-sendgrid/issues/2)
        if (empty($email->getContents())) {
            $email->addContent($message->getBodyContentType(), $message->getBody());
        }

        return $email;
    }

    /**
     * @param mixed $value
     * @return EmailAddress|null
     */
    private function _getEmailAddress($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $fromEmail = key($value);
            $emailAddress = new EmailAddress($fromEmail, $value[$fromEmail]);
        }
        else {
            $emailAddress = new EmailAddress($value);
        }

        return $emailAddress;
    }
}
