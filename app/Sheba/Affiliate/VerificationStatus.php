<?php namespace Sheba\Affiliate;

use Sheba\Helpers\ConstGetter;

class VerificationStatus
{
    use ConstGetter;

    const PENDING = 'pending';
    const VERIFIED = 'verified';
    const UNVERIFIED = 'unverified';
    const REJECTED = 'rejected';
}
