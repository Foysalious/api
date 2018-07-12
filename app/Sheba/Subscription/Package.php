<?php

namespace App\Sheba\Subscription;


interface Package
{
    public function subscribe($billing_type);

    public function unsubscribe();

}