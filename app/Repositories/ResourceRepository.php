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
                $geo_informations = json_decode($partner->geo_informations);
                $partner = collect($partner)->only(['id', 'name','address' , 'sub_domain', 'mobile', 'email', 'status','logo']);
                $partner->put('is_verified', $partner->get('status') == 'Verified' ? 1 : 0);
                $partner->put('is_category_tagged', count($categories) > 0 ?  true : false);
                $partner->put('geo_informations',$geo_informations);
                return $partner;
            }
        }
        return null;
    }

}