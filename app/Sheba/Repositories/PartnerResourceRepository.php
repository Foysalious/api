<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Resource;

class PartnerResourceRepository extends BaseRepository
{
    public function attach(Partner $partner, Resource $resource, $data)
    {
        $data = $this->withUpdateModificationField($data);
        $partner->resources()->attach($resource->id, $data);
        //$partner->update($this->withUpdateModificationField($data));
    }

    public function syncCategories(PartnerResource $partner_resource, $category_ids)
    {
        $partner_resource->categories()->sync($category_ids);
    }
}