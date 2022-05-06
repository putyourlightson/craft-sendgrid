<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sendgrid\mail;

use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendgridTransport extends SendgridApiTransport
{
    private array $_categories;

    public function __construct(string $key, HttpClientInterface $client = null, array $categories = [])
    {
        $this->_categories = $categories;

        parent::__construct($key, $client);
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        foreach ($this->_categories as $category) {
            $email->getHeaders()->add(new TagHeader($category));
        }

        return parent::doSendApi($sentMessage, $email, $envelope);
    }
}
