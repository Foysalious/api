<?php namespace Sheba\Mail;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BusinessMailManager extends MailManager
{
    /**
     * Create an instance of the Mailgun Swift Transport driver.
     *
     * @param  array  $config
     * @return MailgunTransport
     */
    protected function createMailgunTransport(array $config): MailgunTransport
    {
        if (! isset($config['secret'])) {
            $config = $this->app['config']->get('services.mailgun', []);
        }

        return new MailgunTransport(
            $this->guzzle($config),
            $config['secret'],
            $config['business_domain'],
            $config['endpoint'] ?? null
        );
    }

    /**
     * Set a global address on the mailer by type.
     *
     * @param  Mailer  $mailer
     * @param  array  $config
     * @param  string  $type
     * @return void
     */
    protected function setGlobalAddress($mailer, array $config, string $type)
    {
        if ($type != "from") {
            parent::setGlobalAddress($mailer, $config, $type);
            return;
        }

        $address = Arr::get($config, "business_from", $this->app['config']['mail.business_from']);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }
}
