<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Sheba\Reports\UpdateJob as BaseUpdateJob;

class UpdateJob extends BaseUpdateJob
{
    /** @var Customer */
    private $customer;

    /**
     * Create a new job instance.
     *
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
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
        $generator->createOrUpdate($this->customer);
    }
}