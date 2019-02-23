<?php

namespace Sheba\Checkout\Requests;


use App\Models\Service;
use Illuminate\Http\Request;

class PartnerListRequest
{
    private $request;
    private $selectedCategory;
    private $selectedServices;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    private function getCategory($services)
    {
        return (Service::find((int)$services[0]->id))->category;
    }

}