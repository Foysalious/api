<?php

namespace App\Exceptions\RentACar;


use Exception;

class OutsideCityPickUpAddressNotFoundException extends Exception
{
    public function report()
    {
        \Log::debug('PickUpAddress not found for outside city');
    }
}