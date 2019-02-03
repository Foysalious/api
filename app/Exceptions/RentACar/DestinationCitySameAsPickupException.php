<?php
/**
 * Created by PhpStorm.
 * User: arnab
 * Date: 2/3/19
 * Time: 4:55 PM
 */

namespace App\Exceptions\RentACar;


use Exception;

class DestinationCitySameAsPickupException extends Exception
{
    public function report()
    {
        \Log::debug('Pickup and delivery address from same city');
    }
}