<?php namespace Sheba\Mail;

use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\TransportManager;

class BusinessTransportManager extends TransportManager
{
    /**
     * @inheritDoc
     */
    protected function createMailgunDriver(): MailgunTransport
    {
        $config = $this->app['config']->get('services.mailgun', []);

        return new MailgunTransport($this->getHttpClient($config), $config['secret'], $config['business_domain']);
    }
}
