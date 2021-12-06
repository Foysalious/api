<?php namespace Sheba\Mail;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailServiceProvider;
use Swift_Mailer;

class BusinessMailServiceProvider extends MailServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSwiftMailer();

        $this->app->singleton('business_mailer', function ($app) {
            // Once we have created the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new Mailer($app['view'], $app['swift.business_mailer'], $app['events']);

            $this->setMailerDependencies($mailer, $app);

            // If a "from" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "from" address
            // on each one, which makes the developer's life a lot more convenient.
            $from = $app['config']['mail.from'];

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            $to = $app['config']['mail.to'];

            if (is_array($to) && isset($to['address'])) {
                $mailer->alwaysTo($to['address'], $to['name']);
            }

            return $mailer;
        });
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    public function registerSwiftMailer()
    {
        $this->registerSwiftTransport();

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $this->app['swift.business_mailer'] = $this->app->share(function ($app) {
            return new Swift_Mailer($app['swift.business_transport']->driver());
        });
    }

    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    protected function registerSwiftTransport()
    {
        $this->app['swift.business_transport'] = $this->app->share(function ($app) {
            return new BusinessTransportManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['business_mailer', 'swift.business_mailer', 'swift.business_transport'];
    }
}
