<?php namespace App\Jobs\Customer;

use App\Jobs\Job;
use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Voucher\Creator\Referral;

class CreateReferral extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function handle()
    {
        new Referral($this->customer);
    }
}