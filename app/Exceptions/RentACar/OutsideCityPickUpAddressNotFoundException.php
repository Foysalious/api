<?php namespace App\Exceptions\RentACar;

use Exception;
use Illuminate\Support\Facades\Log;

class OutsideCityPickUpAddressNotFoundException extends Exception
{
    public function report()
    {
        Log::debug('PickUpAddress not found for outside city');
    }
}
