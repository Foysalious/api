<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\Resource;

class PartnerResourceRepository extends BaseRepository
{
    public function attach(Partner $partner, Resource $resource, $data)
    {
        $data = $this->withUpdateModificationField($data);
        $partner->resources()->attach($resource->id, $data);
        $partner->update($this->withUpdateModificationField($data));
    }
}