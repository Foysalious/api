<?php namespace App\Exceptions\RentACar;

use App\Exceptions\DoNotThrowException;
use Illuminate\Support\Facades\Log;

class DestinationCitySameAsPickupException extends DoNotThrowException
{
    public function report()
    {
        Log::debug('Pickup and delivery address from same city');
    }
}
