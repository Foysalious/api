<?php namespace App\Repositories;

use App\Http\Controllers\PartnerOrderController;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerWorkingHour;
use App\Models\Resource;
use App\Models\SliderPortal;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Helpers\TimeFrame;
use Sheba\Jobs\JobStatuses;
use Sheba\Manager\JobList;
use Sheba\ModificationFields;
use Sheba\Partner\LeaveStatus;
use Sheba\ResourceScheduler\ResourceHandler;
use Sheba\Location\LocationSetter;

class PartnerRepository
{
    use ModificationFields, CdnFileManager, FileManager, LocationSetter;

    private $partner;
    private $serviceRepo;
    private $features;

    public function __construct($partner)
    {
        $this->partner     = $partner instanceof Partner ? $partner : Partner::find($partner);
        $this->serviceRepo = new ServiceRepository();
        $this->features=['payment_link', 'pos', 'inventory', 'referral', 'due'];
    }

    public function getFeatures()
    {
        return $this->features;
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
                $q->where('status', '<>', JobStatuses::CANCELLED);
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
            $resources = $resources->map(function (Resource $resource) use ($category_id) {
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
            $ongoing_jobs                    = $resource->jobs->whereIn('status', JobStatuses::getOngoingWithoutServed());
            $data['ongoing']                 = $ongoing_jobs->count();
            $data['completed']               = $resource->jobs->where('status', JobStatuses::SERVED)->count();
            $data['name']                    = $resource->profile->name;
            $data['mobile']                  = $resource->profile->mobile;
            $data['picture']                 = $resource->profile->pro_pic;
            $avg_rating                      = $resource->reviews->avg('rating');
            $data['rating']                  = $avg_rating != null ? round($avg_rating, 2) : null;
            $data['joined_at']               = $resource->pivot->created_at ? $resource->pivot->created_at->timestamp : null;
            $data['resource_type']           = $resource->pivot->resource_type;
            $data['is_verified']             = $resource->is_verified;
            $data['is_available']            = $resource->is_tagged;
            $data['booked_jobs']             = [];
            $data['is_tagged']               = $resource->is_tagged;
            $data['total_tagged_categories'] = $resource->total_tagged_categories ?? count($resource->categoriesIn($this->partner->id));
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
        if ( !preg_match('/default/', $this->partner->logo)) {
            $old_logo = substr($this->partner->logo, strlen(env('S3_URL')));
            $this->deleteImageFromCDN($old_logo);
            if ($delete_both && (!preg_match('/default/', $this->partner->logo_original))) {
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

    public function getProfile(Resource $manager_resource)
    {
        $partner        = $this->partner;
        $profile        = $manager_resource->profile;
        $remember_token = $manager_resource->remember_token;
        $token          = (new ProfileRepository())->fetchJWTToken('resource', $manager_resource->id, $remember_token);
        return [
            'remember_token'         => $remember_token,
            'token'                  => $token,
            'id'                     => $partner->id,
            'name'                   => $partner->name,
            'profile'                => [
                'id'      => $profile->id,
                'name'    => $profile->name,
                'pro_pic' => $profile->pro_pic
            ]
        ];
    }
    public function getDashboard(Resource $manager_resource){
        $partner=$this->partner;
        $profile        = $manager_resource->profile;
        $rating = (new ReviewRepository)->getAvgRating($partner->reviews);
        $rating = (string)(is_null($rating) ? 0 : $rating);
        return [
            'logo_original'          => $partner->logo_original,
            'logo'                   => $partner->logo,
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
            'current_subscription_package' => [
                'id' => $partner->subscription->id,
                'name' => $partner->subscription->name,
                'name_bn' => $partner->subscription->show_name_bn,
                'remaining_day' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : 0,
                'billing_type' => $partner->billing_type,
                'rules' => $partner->subscription->getAccessRules(),
                'is_light' => $partner->subscription->id == (int)config('sheba.partner_lite_packages_id')
            ],
            'has_pos_inventory' => $partner->posServices->isEmpty() ? 0 : 1,
            /*'has_pos_due_order' => $total_due_for_pos_orders > 0 ? 1 : 0,
            'has_pos_paid_order' => $has_pos_paid_order,*/
            'has_qr_code' => ($partner->qr_code_image && $partner->qr_code_account_type) ? 1 : 0
        ];
    }
    public function getNewDashboard($request, $performance) {
        $performance->setPartner($this->partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
        $performanceStats = $performance->getData();
        $rating             = (new ReviewRepository)->getAvgRating($this->partner->reviews);
        $rating             = (string)(is_null($rating) ? 0 : $rating);
        $successful_jobs    = $this->partner->jobs()->whereDoesntHave('cancelRequest', function ($q) {
            $q->where('status', CancelRequestStatuses::PENDING)->orWhere('status', CancelRequestStatuses::APPROVED);
        })->select('jobs.id', 'schedule_date', 'status')->get();
        $sales_stats        = (new PartnerSalesStatistics($this->partner))->calculate();
        $upgradable_package = null;
        $new_order          = $this->newOrdersCount($this->partner, $request);
        return [
            'geo_informations'             => json_decode($this->partner->geo_informations),
            'current_subscription_package' => [
                'id'            => $this->partner->subscription->id,
                'name'          => $this->partner->subscription->name,
                'name_bn'       => $this->partner->subscription->show_name_bn,
                'remaining_day' => $this->partner->last_billed_date ? $this->partner->periodicBillingHandler()->remainingDay() : 0,
                'billing_type'  => $this->partner->billing_type,
                'rules'         => $this->partner->subscription->getAccessRules(),
                'is_light'      => $this->partner->subscription->id == (int)config('sheba.partner_lite_packages_id')
            ],
            'badge'                        => $this->partner->resolveBadge(),
            'rating'                       => $rating,
            'status'                       => $this->partner->getStatusToCalculateAccess(),
            'show_status'                  => constants('PARTNER_STATUSES_SHOW')[$this->partner['status']]['partner'],
            'balance'                      => $this->partner->totalWalletAmount(),
            'credit'                       => $this->partner->wallet,
            'is_credit_limit_exceed'       => $this->partner->isCreditLimitExceed(),
            'is_on_leave'                  => $this->partner->runningLeave() ? 1 : 0,
            'bonus_credit'                 => $this->partner->bonusWallet(),
            'current_stats'                => [
                'total_new_order'     => count($new_order) > 0 ? $new_order->total_new_orders : 0,
                'total_order'         => $this->partner->orders()->count(),
                'total_ongoing_order' => (new JobList($this->partner))->ongoing()->count(),
                'today_order'         => $this->partner->todayJobs($successful_jobs)->count(),
                'tomorrow_order'      => $this->partner->tomorrowJobs($successful_jobs)->count(),
                'not_responded'       => $this->partner->notRespondedJobs($successful_jobs)->count(),
                'schedule_due'        => $this->partner->scheduleDueJobs($successful_jobs)->count(),
                'serve_due'           => $this->partner->serveDueJobs($successful_jobs)->count(),
                'complain'            => $this->partner->complains()->notClosed()->count()
            ],
            'sales'                        => [
                'today'                    => [
                    'timeline' => date("jS F", strtotime(Carbon::today())),
                    'amount'   => $sales_stats->today->orderTotalPrice + $sales_stats->today->posSale
                ],
                'week'                     => [
                    'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                    'amount'   => $sales_stats->week->orderTotalPrice + $sales_stats->week->posSale
                ],
                'month'                    => [
                    'timeline' => date("jS F", strtotime(Carbon::today()->startOfMonth())) . "-" . date("jS F", strtotime(Carbon::today())),
                    'amount'   => $sales_stats->month->orderTotalPrice + $sales_stats->month->posSale
                ],
                'total_due_for_pos_orders' => 0,
            ],
            'weekly_performance'           => [
                'timeline'                   => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                'successfully_completed'     => [
                    'count'       => $performanceStats['completed']['total'],
                    'performance' => $this->formatRate($performanceStats['completed']['rate']),
                    'is_improved' => $performanceStats['completed']['is_improved']
                ],
                'completed_without_complain' => [
                    'count'       => $performanceStats['no_complain']['total'],
                    'performance' => $this->formatRate($performanceStats['no_complain']['rate']),
                    'is_improved' => $performanceStats['no_complain']['is_improved']
                ],
                'timely_accepted'            => [
                    'count'       => $performanceStats['timely_accepted']['total'],
                    'performance' => $this->formatRate($performanceStats['timely_accepted']['rate']),
                    'is_improved' => $performanceStats['timely_accepted']['is_improved']
                ],
                'timely_started'             => [
                    'count'       => $performanceStats['timely_processed']['total'],
                    'performance' => $this->formatRate($performanceStats['timely_processed']['rate']),
                    'is_improved' => $performanceStats['timely_processed']['is_improved']
                ]
            ],
            'subscription_promotion'       => $upgradable_package ? [
                'package'         => $upgradable_package->name,
                'package_name_bn' => $upgradable_package->name_bn,
                'package_badge'   => $upgradable_package->badge,
                'package_usp_bn'  => json_decode($upgradable_package->usps, 1)['usp_bn']
            ] : null,
            'leave_info'                   => (new LeaveStatus($this->partner))->getCurrentStatus()
        ];

    }

    private function formatRate($rate)
    {
        if ($rate < 0)
            return 0;
        if ($rate > 100)
            return 100;
        return $rate;
    }

    private function newOrdersCount($partner, $request)
    {
        try {
            $request->merge(['getCount' => 1]);
            $partner_order = new PartnerOrderController();
            $new_order     = $partner_order->newOrders($partner, $request)->getData();
            return $new_order;
        } catch (Throwable $e) {
            return array();
        }
    }


    public function featureVideos($type = null)
    {

        if ($type != null)
            $screens = [$type];
        else
            $screens = $this->features;
        $slides = [];
        $details = [];
        foreach ($screens as $screen) {
            $slider_portals[$screen] = SliderPortal::with('slider.slides')
                ->where('portal_name', 'manager-app')
                ->where('screen', $screen)
                ->get();
            $slides[$screen] = !$slider_portals[$screen]->isEmpty() ? $slider_portals[$screen]->last()->slider->slides->last() : null;

            if ($slides[$screen] && json_decode($slides[$screen]->video_info)) {
                $details[$screen] = json_decode($slides[$screen]->video_info);
            } else
                $details[$screen] = null;
        }

        if ($type){
            return [
                [
                    'key'     => $type,
                    'details' => $details[$type]
                ]
            ];
        }
        return array_map(function($item)use($details){
            return ['key'=>$item,'details'=>$details[$item]];
        }, $screens);
    }

    public function updateWebstoreSettings($data)
    {
        $this->partner->update($data);
    }
}

