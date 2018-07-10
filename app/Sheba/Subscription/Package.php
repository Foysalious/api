<?php

namespace App\Sheba\Subscription;


interface Package
{
    public function subscribe();

    public function unsubscribe();

}