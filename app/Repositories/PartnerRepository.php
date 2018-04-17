<?php

namespace App\Repositories;


use App\Models\Job;
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

    public function resources($type = null, $verify = null, $job_id = null)
    {
        /*$this->partner->load(['resources' => function ($q) use ($type, $verify) {
            $q->select('resources.id', 'profile_id', 'resource_type', 'resources.is_verified')
            ->whereHas('partnerResources', function ($q) {
                $q->has('categories');
            })->with(['jobs' => function ($q) {
                $q->info();
            }])->with('profile', 'reviews');
            if ($type) {
                $q->type($type);
            }
            if ($verify) {
                $q->verified();
            }
        }]);*/

        //$resources = $this->partner->resources()->with('profile', 'reviews', 'jobs');
        //if ($type) $resources->type($type);
        $resources = $this->partner->handymanResources()->get()->unique();
        if ($verify !== null) {
            $resources = $resources->filter(function ($resource) use ($verify) {
                return $resource->is_verified == $verify;
            });
        };

        $job = null;
        if ($job_id != null) {
            $job = Job::find((int)$job_id);
            $resources = $resources->map(function ($resource) use ($job) {
                $is_tagged = $resource->categoriesIn($this->partner->id)->pluck('id')->contains($job->category_id ?: $job->service->category_id);
                return array_add($resource, 'is_tagged', $is_tagged ? 1 : 0);
            });
        }

        return $resources->map(function ($resource) use ($job, $type) {
            $data = [];
            $data['id'] = $resource->id;
            $data['profile_id'] = $resource->profile_id;
            $data['ongoing'] = $resource->jobs->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']])->count();
            $data['completed'] = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $data['name'] = $resource->profile->name;
            $data['mobile'] = $resource->profile->mobile;
            $data['picture'] = $resource->profile->pro_pic;
            $avg_rating = $resource->reviews->avg('rating');
            $data['rating'] = $avg_rating != null ? round($avg_rating, 2) : null;
            $data['joined_at'] = $resource->pivot->created_at->timestamp;
            $data['resource_type'] = $type ?: $resource->pivot->resource_type;
            $data['is_verified'] = $resource->is_verified;
            $data['is_available'] = 1;
            $data['is_tagged'] = $resource->is_tagged;
            if (!empty($job)) {
                if (!scheduler($resource)->isAvailable($job->schedule_date, $job->preferred_time_start)) {
                    $data['is_available'] = 0;
                }
            }
            //removeRelationsAndFields($data);
            return $data;
        });
    }

    public function jobs(Array $statuses, $offset, $limit)
    {
        $this->partner->load(['jobs' => function ($q) use ($statuses, $offset, $limit) {
            $q->info()->status($statuses)->skip($offset)->take($limit)->orderBy('id', 'desc')->with(['jobServices.service', 'category', 'usedMaterials' => function ($q) {
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

    public function hasAppropriateCreditLimit()
    {
        return (double)$this->partner->wallet >= (double)$this->partner->walletSetting->min_wallet_threshold;
    }
}

