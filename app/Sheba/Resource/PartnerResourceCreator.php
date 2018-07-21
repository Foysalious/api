<?php namespace Sheba\Resource;

use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Resource;
use Sheba\Repositories\PartnerResourceRepository;

class PartnerResourceCreator
{
    /** @var Partner */
    private $partner;
    private $data;
    private $resource;

    private $resourceCreator;
    private $partnerResources;

    private $resourceTypes;

    public function __construct(ResourceCreator $resource_creator, PartnerResourceRepository $partner_resources)
    {
        $this->resourceTypes = constants('RESOURCE_TYPES');
        $this->resourceCreator = $resource_creator;
        $this->partnerResources = $partner_resources;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function setData($data)
    {
        $this->data = $data;
        $resource_data = array_except($this->data, ['resource_types', 'category_ids']);
        $this->resourceCreator->setData($resource_data);
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function hasError()
    {
        if (empty($this->resource) && $error = $this->resourceCreator->hasError()) {
            return $error;
        }
        if (!$this->partner->canCreateResource($this->data['resource_types'])) {
            return ['code' => 421, 'msg' => 'Resource cap reached.'];
        }

        return false;
    }

    public function create()
    {
        if (empty($this->resource)) $this->resource = $this->resourceCreator->create();
        $this->associatePartnerResource();
        $this->setResourceCategories();
        $this->notifyPMTeam($this->resource);
    }


    public function associatePartnerResource()
    {
        foreach ($this->data['resource_types'] as $resource_type) {
            $this->partnerResources->attach($this->partner, $this->resource, ['resource_type' => $this->resourceTypes[$resource_type]]);
        }
    }

    private function setResourceCategories()
    {
        $categories = isset($this->data['category_ids']) ? $this->data['category_ids'] : [];
        if (!empty($categories) && !empty($categories[0]) && $handyman_resource = $this->partner->handymanResources()->where('resource_id', $this->resource->id)->first()) {
            $this->partnerResources->syncCategories(PartnerResource::find($handyman_resource->pivot->id), $categories);
        }
    }

    private function notifyPMTeam($resource)
    {
        if ($this->isProfileComplete($resource))
            notify()->department(9)->send($this->createNotificationData($resource));
    }

    private function isProfileComplete($resource)
    {
        if (isset($resource->profile->name) &&
            isset($resource->profile->mobile) &&
            isset($resource->father_name) &&
            ($resource->father_name != "") &&
            isset($resource->nid_no) &&
            isset($resource->address)
        ) return true;
        return false;
    }

    private function createNotificationData($resource)
    {
        return [
            "title" => $this->partner->name . " updated $resource->name profile. Mobile: " . $resource->profile->mobile,
            "link" => config('sheba.admin_url') . "partners/" . $this->partner->id . "#tab_2",
            "type" => notificationType('Warning')
        ];
    }

}