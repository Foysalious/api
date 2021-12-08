<?php namespace Sheba\Mail;

use Illuminate\Mail\MailServiceProvider;

class BusinessMailServiceProvider  extends MailServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('business_mail.manager', function ($app) {
            return new BusinessMailManager($app);
        });

        $this->app->bind('business_mailer', function ($app) {
            return $app->make('business_mail.manager')->mailer();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['business_mailer', 'business_mail.manager'];
    }
}
