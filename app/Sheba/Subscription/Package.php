<?php

namespace App\Sheba\Subscription;


interface Package
{
    public function subscribe($billing_ype);

    public function unsubscribe();

}