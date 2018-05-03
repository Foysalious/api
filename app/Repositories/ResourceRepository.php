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

    public function getPartner($avatar)
    {
        $partners = $this->resource->partners->unique();
        foreach ($partners as $partner) {
            if ($avatar->isManager($partner)) {
                return collect($partner)->only(['id', 'name', 'sub_domain', 'mobile', 'email', 'status']);
            }
        }
        return null;
    }

}