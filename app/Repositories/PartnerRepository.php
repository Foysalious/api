<?php namespace App\Repositories;

use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerWorkingHour;
use App\Models\Resource;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\ResourceScheduler\ResourceHandler;

class PartnerRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    private $partner;
    private $serviceRepo;

    public function __construct($partner)
    {
        $this->partner     = $partner instanceof Partner ? $partner : Partner::find($partner);
        $this->serviceRepo = new ServiceRepository();
    }

    /**
     * @param null $verify
     * @param null $category_id
     * @param null $date
     * @param null $preferred_time
     * @param Job|null $job
     * @param SubscriptionOrder|null $subscription_order
     * @return Collection
     */
    public function resources($verify = null, $category_id = null, $date = null, $preferred_time = null, Job $job = null, SubscriptionOrder $subscription_order = null)
    {
        $resources = $this->partner->resources()->get()->unique();
        $resources->load([
            'jobs'    => function ($q) {
                $q->where('status', '<>', constants('JOB_STATUSES')['Cancelled']);
            },
            'profile' => function ($q) {
                $q->select('id', 'name', 'mobile', 'pro_pic');
            },
            'reviews' => function ($q) {
                $q->select('id', 'rating', 'resource_id', 'category_id');
            }
        ]);
        if ($verify !== null && !$this->partner->isLite()) {
            $resources = $resources->filter(function ($resource) use ($verify) {
                return $resource->is_verified == $verify;
            });
        };
        $job = null;
        if ($category_id != null) {
            $resources = $resources->map(function ($resource) use ($category_id) {
                $resource_categories = $resource->categoriesIn($this->partner->id);
                $is_tagged           = $resource_categories->pluck('id')->contains($category_id);
                array_add($resource, 'is_tagged', $is_tagged ? 1 : 0);
                array_add($resource, 'total_tagged_categories', count($resource_categories));
                return $resource;
            });
        }
        return $resources->map(function ($resource) use ($category_id, $date, $preferred_time, $job, $subscription_order) {
            $data                            = [];
            $data['id']                      = $resource->id;
            $data['profile_id']              = $resource->profile_id;
            $ongoing_jobs                    = $resource->jobs->whereIn('status', [
                constants('JOB_STATUSES')['Serve_Due'],
                constants('JOB_STATUSES')['Accepted'],
                constants('JOB_STATUSES')['Process'],
                constants('JOB_STATUSES')['Schedule_Due']
            ]);
            $data['ongoing']                 = $ongoing_jobs->count();
            $data['completed']               = $resource->jobs->where('status', constants('JOB_STATUSES')['Served'])->count();
            $data['name']                    = $resource->profile->name;
            $data['mobile']                  = $resource->profile->mobile;
            $data['picture']                 = $resource->profile->pro_pic;
            $avg_rating                      = $resource->reviews->avg('rating');
            $data['rating']                  = $avg_rating != null ? round($avg_rating, 2) : null;
            $data['joined_at']               = $resource->pivot->created_at->timestamp;
            $data['resource_type']           = $resource->pivot->resource_type;
            $data['is_verified']             = $resource->is_verified;
            $data['is_available']            = $resource->is_tagged;
            $data['booked_jobs']             = [];
            $data['is_tagged']               = $resource->is_tagged;
            $data['total_tagged_categories'] = isset($resource->total_tagged_categories) ? count($resource->total_tagged_categories) : count($resource->categoriesIn($this->partner->id));
            if ($category_id) {
                $category = Category::find($category_id);
                if (in_array($category_id, array_map('intval', explode(',', env('RENT_CAR_IDS'))))) {
                    foreach ($ongoing_jobs->where('resource_id', $resource->id)->where('category_id', $category_id) as $job) {
                        array_push($data['booked_jobs'], [
                            'job_id'           => $job->id,
                            'partner_order_id' => $job->partnerOrder->id,
                            'code'             => $job->partnerOrder->order->code()
                        ]);
                    }
                } else {
                    $resource_scheduler = scheduler($resource);
                    if ($subscription_order) {
                        foreach (json_decode($subscription_order->schedules) as $schedule) {
                            $preferred_subscription_order_time = Carbon::parse(explode('-', $schedule->time)[0])->format('H:s:i');
                            if (!$resource_scheduler->isAvailableForCategory($schedule->date, $preferred_subscription_order_time, $category)) {
                                $data = $this->getBookedJobs($data, $resource_scheduler);
                            }
                        }
                    } elseif (!$resource_scheduler->isAvailableForCategory($date, explode('-', $preferred_time)[0], $category, $job)) {
                        $data = $this->getBookedJobs($data, $resource_scheduler);
                    }
                }
            }
            return $data;
        });
    }

    /**
     * @param array $data
     * @param ResourceHandler $resource_scheduler
     * @return array
     */
    private function getBookedJobs(array &$data, ResourceHandler $resource_scheduler)
    {
        $data['is_available'] = 0;
        foreach ($resource_scheduler->getBookedJobs() as $job) {
            array_push($data['booked_jobs'], [
                'job_id'           => $job->id,
                'partner_order_id' => $job->partnerOrder->id,
                'code'             => $job->partnerOrder->order->code()
            ]);
        }
        return $data;
    }

    /**
     * @param array $statuses
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function jobs(Array $statuses, $offset, $limit)
    {
        $this->partner->load([
            'jobs' => function ($q) use ($statuses, $offset, $limit) {
                $q->info()->status($statuses)->skip($offset)->take($limit)->orderBy('id', 'desc')->with([
                    'jobServices.service',
                    'cancelRequests',
                    'category',
                    'usedMaterials' => function ($q) {
                        $q->select('id', 'job_id', 'material_name', 'material_price');
                    },
                    'resource.profile',
                    'review',
                    'partner_order' => function ($q) {
                        $q->with([
                            'order' => function ($q) {
                                $q->with('location', 'customer.profile');
                            }
                        ]);
                    }
                ]);
            }
        ]);
        return $this->partner->jobs;
    }

    public function resolveStatus($status)
    {
        if ($status == 'new') {
            return array(
                constants('JOB_STATUSES')['Pending'],
                constants('JOB_STATUSES')['Not_Responded']
            );
        } elseif ($status == 'ongoing') {
            return array(
                constants('JOB_STATUSES')['Serve_Due'],
                constants('JOB_STATUSES')['Accepted'],
                constants('JOB_STATUSES')['Process'],
                constants('JOB_STATUSES')['Schedule_Due']
            );
        } elseif ($status == 'history') {
            return array(constants('JOB_STATUSES')['Served']);
        }
    }

    public function hasAppropriateCreditLimit()
    {
        return (double)$this->partner->wallet >= (double)$this->partner->walletSetting->min_wallet_threshold;
    }

    public function getLocations()
    {
        if ($this->partner->geo_informations) {
            $geo_info = json_decode($this->partner->geo_informations);
            if ($geo_info) {
                $hyper_locations = HyperLocal::insideCircle($geo_info)->with('location')->get()->filter(function ($item) {
                        return !empty($item->location);
                    })->pluck('location');
                return $hyper_locations;
            } else {
                return [];
            }
        } else {
            return Location::published()->select('id', 'name')->get();
        }

    }

    public function saveDefaultWorkingHours($by)
    {
        $default_working_days  = getDefaultWorkingDays();
        $default_working_hours = getDefaultWorkingHours();
        foreach ($default_working_days as $day) {
            $this->partner->workingHours()->save(new PartnerWorkingHour(array_merge($by, [
                'day'        => $day,
                'start_time' => $default_working_hours->start_time,
                'end_time'   => $default_working_hours->end_time
            ])));
        }
    }

    /**
     * Update logo for partner.
     *
     * @param $request
     * @return string
     */
    public function updateLogo($request)
    {
        $this->_deleteOldLogo();
        $data['logo_original'] = $this->saveLogo($request);
        $data['logo']          = $data['logo_original'];
        return $this->partner->update($data);
    }

    /**
     * Delete old logo of the partner from cdn.
     *
     * @param $delete_both
     */
    private function _deleteOldLogo($delete_both = true)
    {
        if ($this->partner->logo != getPartnerDefaultLogo()) {
            $old_logo = substr($this->partner->logo, strlen(env('S3_URL')));
            $this->deleteImageFromCDN($old_logo);
            if ($delete_both && ($this->partner->logo_original != getPartnerDefaultLogo())) {
                $old_logo_original = substr($this->partner->logo_original, strlen(env('S3_URL')));
                $this->deleteImageFromCDN($old_logo_original);
            }
        }
    }

    /**
     * Save logo for partner to cdn.
     *
     * @param $request
     * @return string
     */
    public function saveLogo($request)
    {
        list($logo, $logo_filename) = $this->makeThumb($request->file('logo'), $this->partner->name);
        return $this->saveImageToCDN($logo, getPartnerLogoFolder(), $logo_filename);
    }

    public function validatePartner($remember_token)
    {
        $manager_resource = Resource::where('remember_token', $remember_token)->first();
        if (isset($manager_resource) && isset($this->partner) && $manager_resource->isManager($this->partner)) {
            return $this->partner;
        } else {
            return false;
        }
    }

    public function getDashboard(Resource $manager_resource)
    {
        $partner        = $this->partner;
        $profile        = $manager_resource->profile;
        $remember_token = $manager_resource->remember_token;
        $token          = (new ProfileRepository())->fetchJWTToken('resource', $manager_resource->id, $remember_token);
        $rating = (new ReviewRepository)->getAvgRating($partner->reviews);
        $rating = (string)(is_null($rating) ? 0 : $rating);
        return [
            'remember_token'         => $remember_token,
            'token'                  => $token,
            'id'                     => $partner->id,
            'name'                   => $partner->name,
            'logo'                   => $partner->logo,
            'logo_original'          => $partner->logo_original,
            'profile'                => [
                'id'      => $profile->id,
                'name'    => $profile->name,
                'pro_pic' => $profile->pro_pic
            ],
            'badge'                  => $partner->resolveBadge(),
            'rating'                 => $rating,
            'status'                 => $partner->getStatusToCalculateAccess(),
            'show_status'            => constants('PARTNER_STATUSES_SHOW')[$partner['status']]['partner'],
            'balance'                => $partner->totalWalletAmount(),
            'credit'                 => $partner->wallet,
            'bonus'                  => round($partner->bonusWallet(), 2),
            'is_credit_limit_exceed' => $partner->isCreditLimitExceed(),
            'is_on_leave'            => $partner->runningLeave() ? 1 : 0,
            'bonus_credit'           => $partner->bonusWallet(),
            'reward_point'           => $partner->reward_point,
            'bkash_no'               => $partner->bkash_no,
            'is_nid_verified'        => (int)$profile->nid_verified ? true : false,
        ];
    }
}

