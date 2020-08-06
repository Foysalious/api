<?php namespace App\Exceptions\RentACar;

use App\Exceptions\ApiValidationException;
use Illuminate\Support\Facades\Log;

class DestinationCitySameAsPickupException extends ApiValidationException
{
    public function report()
    {
        Log::debug('Pickup and delivery address from same city');
    }
}
