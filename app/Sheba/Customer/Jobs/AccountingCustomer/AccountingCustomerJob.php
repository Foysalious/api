<?php namespace App\Sheba\Customer\Jobs\AccountingCustomer;

use App\Jobs\Job;
use App\Sheba\Customer\AccountingCustomerCreator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountingCustomerJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 2) return;
        try {
            /** @var AccountingCustomerCreator $service */
            $service = app(AccountingCustomerCreator::class);
            $service->setPartnerId($this->event->getCustomerPartnerID())->setCustomerId($this->event->getCustomerId())->setCustomerMobile($this->event->getCustomerMobile())->setCustomerName($this->event->getCustomerName())->setCustomerProfilePicture($this->event->getCustomerProfilePicture())->storeAccountingCustomer();
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }
}
