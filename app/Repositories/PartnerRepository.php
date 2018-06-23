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
        $resources = $this->partner->handymanResources()->get()->unique();
        $resources->load('jobs', 'profile', 'reviews');
        if ($verify !== null) {
            $resources = $resources->filter(function ($resource) use ($verify) {
                return $resource->is_verified == $verify;
            });
        };

        $job = null;
        if ($job_id != null) {
            $job = Job::find((int)$job_id);
            $resources = $resources->map(function ($resource) use ($job) {
                $resource_categories = $resource->categoriesIn($this->partner->id);
                $is_tagged = $resource_categories->pluck('id')->contains($job->category_id ?: $job->service->category_id);
                array_add($resource, 'is_tagged', $is_tagged ? 1 : 0);
                array_add($resource, 'total_tagged_categories', count($resource_categories));
                return $resource;
            });
        }

        return $resources->map(function ($resource) use ($job, $type) {
            $data = [];
            $data['id'] = $resource->id;
            $data['profile_id'] = $resource->profile_id;
            $ongoing_jobs = $resource->jobs->whereIn('status', [constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']]);
            $data['ongoing'] = $ongoing_jobs->count();
            $data['completed'] = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $data['name'] = $resource->profile->name;
            $data['mobile'] = $resource->profile->mobile;
            $data['picture'] = $resource->profile->pro_pic;
            $avg_rating = $resource->reviews->avg('rating');
            $data['rating'] = $avg_rating != null ? round($avg_rating, 2) : null;
            $data['joined_at'] = $resource->pivot->created_at->timestamp;
            $data['resource_type'] = $type ?: $resource->pivot->resource_type;
            $data['is_verified'] = $resource->is_verified;
            $data['is_available'] = $resource->is_tagged;
            $data['booked_jobs'] = [];
            $data['is_tagged'] = $resource->is_tagged;
            $data['total_tagged_categories'] = isset($resource->total_tagged_categories) ? count($resource->total_tagged_categories) : count($resource->categoriesIn($this->partner->id));
            if (!empty($job)) {
                if (in_array($job->category_id, array_map('intval', explode(',', env('RENT_CAR_IDS'))))) {
                    foreach ($ongoing_jobs->where('resource_id', $resource->id)->where('category_id', $job->category_id) as $job) {
                        array_push($data['booked_jobs'], array(
                            'job_id' => $job->id,
                            'partner_order_id' => $job->partnerOrder->id,
                            'code' => $job->partnerOrder->order->code()
                        ));
                    }
                } else {
                    $resource_scheduler = scheduler($resource);
                    if (!$resource_scheduler->isAvailableForCategory($job->schedule_date, $job->preferred_time_start, $job->category)) {
                        $data['is_available'] = 0;
                        foreach ($resource_scheduler->getBookedJobs() as $job) {
                            array_push($data['booked_jobs'], array(
                                'job_id' => $job->id,
                                'partner_order_id' => $job->partnerOrder->id,
                                'code' => $job->partnerOrder->order->code()
                            ));
                        }
                    }
                }
            }
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
            return array(constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']);
        } elseif ($status == 'history') {
            return array(constants('JOB_STATUSES')['Served']);
        }
    }

    public function hasAppropriateCreditLimit()
    {
        return (double)$this->partner->wallet >= (double)$this->partner->walletSetting->min_wallet_threshold;
    }
}

