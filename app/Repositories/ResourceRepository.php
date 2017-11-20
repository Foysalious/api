<?php

namespace App\Repositories;


use App\Models\Resource;

class ResourceRepository
{
    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource instanceof Resource ? $resource : Resource::find($resource);
    }

    public function getPartner()
    {
        $partner = $this->resource->firstPartner();
        return count($partner) > 0 ? collect($partner)->only(['id', 'name', 'sub_domain', 'mobile', 'email']) : null;
    }

}