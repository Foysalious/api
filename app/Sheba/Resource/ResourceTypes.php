<?php namespace Sheba\Resource;

use Sheba\Helpers\ConstGetter;

class ResourceTypes
{
    use ConstGetter;

    const ADMIN = 'Admin';
    const OPERATION = 'Operation';
    const FINANCE = 'Finance';
    const HANDYMAN = 'Handyman';
    const SALESMAN = 'Salesman';
}
