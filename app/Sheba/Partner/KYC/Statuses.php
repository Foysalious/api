<?php namespace App\Sheba\Partner\KYC;

use Sheba\Helpers\ConstGetter;


class Statuses
{
    use ConstGetter;
    const UNVERIFIED = 'unverified';
    const PENDING = 'pending';
    const VERIFIED = 'verified';
    const REJECTED = 'rejected';

}