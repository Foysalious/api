<?php

namespace App\Repositories;


use App\Models\Partner;
use Illuminate\Database\QueryException;

class PartnerRepository
{
    private $partner;
    private $serviceRepo;

    public function __construct($partner)
    {
        $this->partner = $partner instanceof Partner ? $partner : Partner::find($partner);
        $this->serviceRepo = new ServiceRepository();
    }

    public function resources($type = null, $verify = null)
    {
        $this->partner->load(['resources' => function ($q) use ($type, $verify) {
            $q->select('resources.id', 'profile_id', 'resource_type', 'resources.is_verified')->with(['jobs' => function ($q) {
                $q->info()->status([constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']]);
            }])->with('profile', 'reviews');
            if ($type) {
                $q->type($type);
            }
            if ($verify) {
                $q->verified();
            }
        }]);
        foreach ($this->partner->resources as $resource) {
            $resource['ongoing'] = $resource->jobs->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process']])->count();
            $resource['completed'] = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $resource['name'] = $resource->profile->name;
            $resource['mobile'] = $resource->profile->mobile;
            $resource['picture'] = $resource->profile->pro_pic;
            $resource['rating'] = $resource->reviews->avg('rating') != null ? round($resource->reviews->avg('rating'), 2) : null;
            $resource['joined_at'] = $resource->pivot->created_at->timestamp;
            $this->serviceRepo->removeRelationsFromModel($resource, $resource->getRelations());
        }
        return $this->partner->resources;
    }

    public function jobs(Array $statuses)
    {
        $this->partner->load(['jobs' => function ($q) use ($statuses) {
            $q->info()->status($statuses)->with(['usedMaterials' => function ($q) {
                $q->select('id', 'job_id', 'material_name', 'material_price');
            }, 'resource.profile', 'review', 'partner_order' => function ($q) {
                $q->with(['order' => function ($q) {
                    $q->with('location', 'customer.profile');
                }]);
            }]);
        }]);
        return $this->partner->jobs;
    }

    public function resolveStatus($status)
    {
        if ($status == 'new') {
            return array(constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded']);
        } elseif ($status == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']);
        } elseif ($status == 'history') {
            return array(constants('JOB_STATUSES')['Served']);
        }
    }

}

