<?php namespace App\Exceptions\RentACar;

use Exception;
use Illuminate\Support\Facades\Log;

class DestinationCitySameAsPickupException extends Exception
{
    public function report()
    {
        Log::debug('Pickup and delivery address from same city');
    }
}
