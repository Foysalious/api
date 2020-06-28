<?php namespace Sheba\AutoSpAssign\Job;


use App\Jobs\Job;
use App\Models\Customer;
use App\Models\PartnerOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\AutoSpAssign\Initiator;

class InitiateAutoSpAssign extends Job
{
    use InteractsWithQueue, SerializesModels;

    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var Customer */
    private $customer;
    /** @var array */
    private $partnerId;


    public function __construct(PartnerOrder $partner_order, Customer $customer, array $partner_id)
    {
        $this->partnerOrder = $partner_order;
        $this->customer = $customer;
        $this->partnerId = $partner_id;
    }

    public function handle()
    {
        if ($this->attempts() < 3) return;
        /** @var Initiator $initiator */
        $initiator = app(Initiator::class);
        $initiator->setPartnerIds($this->partnerId)->setCustomer($this->customer)->setPartnerOrder($this->partnerOrder)->initiate();
    }
}