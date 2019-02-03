<?php

namespace App\Exceptions\RentACar;


use Exception;

class PickUpAddressNotFoundException extends Exception
{
    public function report()
    {
        \Log::debug('PickUpAddress not found');
    }
}