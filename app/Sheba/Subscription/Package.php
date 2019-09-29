<?php

namespace Sheba\Subscription;


interface Package
{
    public function subscribe($billing_type, $discount_id);

    public function unsubscribe();

}