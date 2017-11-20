<?php

namespace App\Repositories;


use App\Models\Partner;

class PartnerRepository
{
    private $partner;
    private $serviceRepo;

    public function __construct($partner)
    {
        $this->partner = $partner instanceof Partner ? $partner : Partner::find($partner);
        $this->serviceRepo = new ServiceRepository();
    }

    public function resources()
    {
        $this->partner->load(['resources' => function ($q) {
            $q->select('resources.id', 'profile_id', 'resources.is_verified')->verified()->with(['jobs' => function ($q) {
                $q->info()->status([constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']]);
            }])->with('profile', 'reviews');
        }]);
        foreach ($this->partner->resources as $resource) {
            $resource['ongoing'] = $resource->jobs->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process']])->count();
            $resource['completed'] = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $resource['name'] = $resource->profile->name;
            $resource['mobile'] = $resource->profile->mobile;
            $resource['rating'] = $resource->reviews->avg('rating') != null ? round($resource->reviews->avg('rating'), 2) : null;
            $this->serviceRepo->removeRelationsFromModel($resource, $resource->getRelations());
        }
        return $this->partner->resources;
    }


}

