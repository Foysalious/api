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
                $categories = $partner->categories;
                $partner = collect($partner)->only(['id', 'name','address' , 'sub_domain', 'mobile', 'email', 'status','geo_informations','logo']);
                $partner->put('is_verified', $partner->get('status') == 'Verified' ? 1 : 0);
                $partner->put('categories', $categories);
                return $partner;
            }
            if(!$avatar->isManager($partner)) {
                $partner = collect($partner)->only(['id', 'name','logo']);
                return $partner;
            }
        }
        return null;
    }

}