<?php namespace App\Exceptions\RentACar;

use App\Exceptions\DoNotReportException;
use Illuminate\Support\Facades\Log;

class DestinationCitySameAsPickupException extends DoNotReportException
{
    public function report()
    {
        Log::debug('Pickup and delivery address from same city');
    }
}
