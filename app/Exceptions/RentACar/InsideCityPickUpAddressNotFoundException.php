<?php

namespace App\Exceptions\RentACar;


use Exception;

class InsideCityPickUpAddressNotFoundException extends Exception
{

    public function report()
    {
        \Log::debug('PickUpAddress not found for inside city');
    }
}