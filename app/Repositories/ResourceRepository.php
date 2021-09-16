<?php namespace App\Repositories;

use App\Models\Resource;
use Sheba\Dal\ResourceStatusChangeLog\Model as ResourceStatusChangeLog;
use Sheba\ModificationFields;

class ResourceRepository
{
    use ModificationFields;

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
            if (!$avatar->isManager($partner)) {
                $partner = collect($partner)->only(['id', 'name','logo']);
                return $partner;
            }
        }
        return null;
    }

    public function setToPendingStatus()
    {
        $this->shootStatusChangeLog();
        $this->resource->update($this->withUpdateModificationField(['status' => 'pending']));
    }

    private function shootStatusChangeLog()
    {
        $data = [
            'from' => $this->resource->status,
            'to' => 'pending',
            'resource_id' => $this->resource->id,
            'reason' => 'nid_info_submit',
            'log' => 'status changed to pending as resource submit profile info for verification'
        ];

        ResourceStatusChangeLog::create($this->withCreateModificationField($data));
    }

    public function update($data)
    {
        $this->resource->update($this->withUpdateModificationField($data));
    }
}
