<?php namespace Sheba\Reports\PartnerOrder;

use App\Models\Customer;
use App\Models\PartnerOrder;
use Sheba\Reports\UpdateJob as BaseUpdateJob;

class UpdateJob extends BaseUpdateJob
{
    /** @var PartnerOrder */
    private $partnerOrder;

    /**
     * Create a new job instance.
     *
     * @param PartnerOrder $partner_order
     */
    public function __construct(PartnerOrder $partner_order)
    {
        $this->partnerOrder = $partner_order;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @param Generator $generator
     * @return void
     */
    public function handle(Generator $generator)
    {
        $generator->createOrUpdate($this->partnerOrder);
    }
}