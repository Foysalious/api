<?php namespace Sheba\Resource;

use App\Models\Partner;
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

    public function __construct(ResourceCreator $resource_creator, PartnerResourceRepository $partner_resources)
    {
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
        $this->resourceCreator->setData($data);
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function hasError()
    {
        if($error = $this->resourceCreator->hasError()) {
            return $error;
        }

        if(!$this->partner->canCreateResource($this->data['resource_type'])) {
            return ['code' => 421, 'msg' => 'Resource cap reached.'];
        }

        return false;
    }

    public function create()
    {
        if(empty($this->resource)) $this->resource = $this->resourceCreator->create();
        $this->partnerResources->attach($this->partner, $this->resource, ['resource_type' => $this->data['resource_type']]);
    }

}