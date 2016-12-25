<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Job;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function giveReview(Customer $customer, Job $job)
    {
        dd($customer->orders->jobs);
    }
}
