<?php namespace Sheba\Jobs;

use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Partner;
use App\Models\Resource;
use Sheba\Jobs\StatusChanger as JobStatusChanger;

class AcceptJobAndAssignResource
{
    protected $job;
    protected $partner;
    protected $resource;
    protected $request;
    protected $statusChanger;

    public function __construct(JobStatusChanger $statusChanger)
    {
        $this->statusChanger = $statusChanger;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function acceptJobAndAssignResource()
    {
        $this->request->merge(['job' => $this->job]);
        $this->request->merge(['manager_resource' => $this->resource]);
        $this->request->merge(['resource_id' => $this->resource->id]);
        $this->request->merge(['partner' => $this->partner]);
        $this->statusChanger->acceptJobAndAssignResource($this->request);
    }
}