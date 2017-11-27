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

    public function resources($type = 'Handyman')
    {
        $this->partner->load(['resources' => function ($q) use ($type) {
            $q->select('resources.id', 'profile_id', 'resources.is_verified')->verified()->type($type)->with(['jobs' => function ($q) {
                $q->info()->status([constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']]);
            }])->with('profile', 'reviews');
        }]);
        foreach ($this->partner->resources as $resource) {
            $resource['ongoing'] = $resource->jobs->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process']])->count();
            $resource['completed'] = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $resource['name'] = $resource->profile->name;
            $resource['mobile'] = $resource->profile->mobile;
            $resource['picture'] = $resource->profile->pro_pic;
            $resource['rating'] = $resource->reviews->avg('rating') != null ? round($resource->reviews->avg('rating'), 2) : null;
            $this->serviceRepo->removeRelationsFromModel($resource, $resource->getRelations());
        }
        return $this->partner->resources;
    }

    public function jobs(Array $statuses)
    {
        $this->partner->load(['jobs' => function ($q) use ($statuses) {
            $q->info()->status($statuses)->with(['resource.profile', 'review', 'partner_order.order.location']);
        }]);
        return $this->partner->jobs;
    }

    public function resolveStatus($status)
    {
        if ($status == 'new') {
            return array(constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded']);
        } elseif ($status == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process']);
        }
    }

}

