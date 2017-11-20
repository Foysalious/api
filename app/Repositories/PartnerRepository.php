<?php

namespace App\Repositories;


use App\Models\Partner;

class PartnerRepository
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = $partner instanceof Partner ? $partner : Partner::find($partner);
    }

}