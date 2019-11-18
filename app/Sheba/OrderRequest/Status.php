<?php namespace Sheba\OrderRequest;

use Sheba\Helpers\ConstGetter;

class Status
{
    use ConstGetter;

    const CONFIRMED = 'pending';
    const ACCEPTED = 'accepted';
    const DECLINED = 'declined';
    const NOT_RESPONDED = 'not_responded';
    const MISSED = 'missed';
}
