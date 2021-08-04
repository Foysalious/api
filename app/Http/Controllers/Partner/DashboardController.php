<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PartnerOrderController;
use App\Http\Controllers\SpLoanInformationCompletion;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\Resource;
use App\Models\SliderPortal;
use App\Repositories\DiscountRepository;
use App\Repositories\FileRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\ResourceJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\Partner\KYC\Statuses;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Helpers\TimeFrame;
use Sheba\Location\LocationSetter;
use Sheba\Manager\JobList;
use Sheba\ModificationFields;
use Sheba\Partner\HomePageSetting\CacheManager;
use Sheba\Partner\HomePageSetting\Setting;
use Sheba\Partner\HomePageSettingV3\DefaultSettingV3;
use Sheba\Partner\HomePageSettingV3\NewFeatures;
use Sheba\Partner\HomePageSettingV3\SettingV3;
use Sheba\Partner\LeaveStatus;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Reward\PartnerReward;
use Throwable;

class DashboardController extends Controller
{
    use ModificationFields, LocationSetter;

    public function get(Request $request, PartnerPerformance $performance)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 660);
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            $slider_portal = SliderPortal::with('slider.slides')->where('portal_name', 'manager-app')->where('screen', 'home')->get();
            $slides_query = !$slider_portal->isEmpty() ? $slider_portal->last()->slider->slides()->where('location_id', $this->location)->orderBy('id', 'desc') : null;
            $slide        = null;
            $all_slides   = $slides_query ? $slides_query->get() : null;
            $videos       = [];
            if ($all_slides && !$all_slides->isEmpty()) {
                foreach ($all_slides as $key => $item) {
                    if ($item && json_decode($item->video_info)) {
                        if ($key == 0)
                            $slide = $item;
                        array_push($videos, json_decode($item->video_info));
                    }
                }
            }
            $details = (new PartnerRepository($partner))->featureVideos();
            $performance->setPartner($partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
            $performanceStats = $performance->getData();
            $rating             = (new ReviewRepository)->getAvgRating($partner->reviews);
            $rating             = (string)(is_null($rating) ? 0 : $rating);
            $successful_jobs    = $partner->jobs()->whereDoesntHave('cancelRequest', function ($q) {
                $q->where('status', CancelRequestStatuses::PENDING)->orWhere('status', CancelRequestStatuses::APPROVED);
            })->select('jobs.id', 'schedule_date', 'status')->get();
            $sales_stats        = (new PartnerSalesStatistics($partner))->calculate();
            $upgradable_package = null;
            $new_order          = $this->newOrdersCount($partner, $request);
            $dashboard = [
                'name'                         => $partner->name,
                'logo'                         => $partner->logo,
                'geo_informations'             => json_decode($partner->geo_informations),
                'current_subscription_package' => [
                    'id'            => $partner->subscription->id,
                    'name'          => $partner->subscription->name,
                    'name_bn'       => $partner->subscription->show_name_bn,
                    'remaining_day' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : 0,
                    'billing_type'  => $partner->billing_type,
                    'rules'         => $partner->subscription->getAccessRules(),
                    'is_light'      => $partner->subscription->id == (int)config('sheba.partner_lite_packages_id')
                ],
                'badge'                        => $partner->resolveBadge(),
                'rating'                       => $rating,
                'status'                       => $partner->getStatusToCalculateAccess(),
                'show_status'                  => constants('PARTNER_STATUSES_SHOW')[$partner['status']]['partner'],
                'balance'                      => $partner->totalWalletAmount(),
                'credit'                       => $partner->wallet,
                'is_credit_limit_exceed'       => $partner->isCreditLimitExceed(),
                'is_on_leave'                  => $partner->runningLeave() ? 1 : 0,
                'bonus_credit'                 => $partner->bonusWallet(),
                'reward_point'                 => $partner->reward_point,
                'bkash_no'                     => $partner->bkash_no,
                'current_stats'                => [
                    'total_new_order'     => count($new_order) > 0 ? $new_order->total_new_orders : 0,
                    'total_order'         => $partner->orders()->count(),
                    'total_ongoing_order' => (new JobList($partner))->ongoing()->count(),
                    'today_order'         => $partner->todayJobs($successful_jobs)->count(),
                    'tomorrow_order'      => $partner->tomorrowJobs($successful_jobs)->count(),
                    'not_responded'       => $partner->notRespondedJobs($successful_jobs)->count(),
                    'schedule_due'        => $partner->scheduleDueJobs($successful_jobs)->count(),
                    'serve_due'           => $partner->serveDueJobs($successful_jobs)->count(),
                    'complain'            => $partner->complains()->notClosed()->count()
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
                'is_nid_verified'              => (int)$request->manager_resource->profile->nid_verified ? true : false,
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
                'leave_info'                   => (new LeaveStatus())->setArtisan($partner)->getCurrentStatus(),
                'sheba_order'                  => $partner->orders->isEmpty() ? 0 : 1,
                'manager_dashboard_banner'     => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner_assets/dashboard/manager_dashboard.png',
                'video'                        => $slide ? json_decode($slide->video_info) : null,
                'has_pos_inventory'            => $partner->posServices->isEmpty() ? 0 : 1,
                'has_kyc_profile_completed'    => $this->getSpLoanInformationCompletion($partner, $request),
                'has_pos_due_order'            => 0,
                'has_pos_paid_order'           => 0,
                'home_videos'    => $videos ? $videos : null,
                'feature_videos' => $details,
                'has_qr_code'    => ($partner->qr_code_image && $partner->qr_code_account_type) ? 1 : 0,
                'has_webstore'   => $partner->has_webstore,
                'is_webstore_published' => $partner->is_webstore_published,
                'is_registered_for_delivery' => $partner->deliveryInformation ? 1 : 0
            ];
            if (request()->hasHeader('Portal-Name'))
                $this->setDailyUsageRecord($partner, request()->header('Portal-Name'));
            return api_response($request, $dashboard, 200, ['data' => $dashboard]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSpLoanInformationCompletion($partner, $request)
    {
        try {
            $sp_loan_information_completion = new SpLoanInformationCompletion();
            $sp_information_completion      = $sp_loan_information_completion->getLoanInformationCompletion($partner, $request)->getData()->completion;
            $personal                       = $sp_information_completion->personal->completion_percentage;
            $business                       = $sp_information_completion->business->completion_percentage;
            $finance                        = $sp_information_completion->finance->completion_percentage;
            $nominee                        = $sp_information_completion->nominee->completion_percentage;
            $documents                      = $sp_information_completion->documents->completion_percentage;
            return ($personal == 100 && $business == 100 && $finance == 100 && $nominee == 100 && $documents == 100) ? 1 : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function getV3DashBoard(Request $request, PartnerPerformance $performance) {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 660);
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            $data     = (new PartnerRepository($partner))->getNewDashboard($request, $performance);
            if (request()->hasHeader('Portal-Name'))
                $this->setDailyUsageRecord($partner, request()->header('Portal-Name'));
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNewHomePage(Request $request) {
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            /** @var Resource $resource */
            $resource = $request->manager_resource;
            $resource_status = $resource->status;
            $data = [
                'name'                         => $partner->name,
                'logo'                         => $partner->logo,
                'resource_kyc_status'          => $resource_status,
                'is_nid_verified'              => (bool)((int)$request->manager_resource->profile->nid_verified),
                'is_webstore_published'        =>$partner->is_webstore_published,
                'new_notification_count'       => $partner->notifications()->where('is_seen', '0')->count()
            ];
            if ($resource_status === Statuses::VERIFIED){
                $data['message_seen'] = (bool)((int)$resource->verification_message_seen);
            }
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
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

    private function formatRate($rate)
    {
        if ($rate < 0)
            return 0;
        if ($rate > 100)
            return 100;
        return $rate;
    }

    /**
     * @param Partner $partner
     * @param $portal_name
     */
    private function setDailyUsageRecord(Partner $partner, $portal_name)
    {
        $daily_usages_record_namespace = 'PartnerDailyAppUsages:partner_' . $partner->id;
        $daily_uses_count              = Redis::get($daily_usages_record_namespace);
        $daily_uses_count              = !is_null($daily_uses_count) ? (int)$daily_uses_count + 1 : 1;
        $second_left = Carbon::now()->diffInSeconds(Carbon::today()->endOfDay(), false);
        Redis::set($daily_usages_record_namespace, $daily_uses_count);
        if ($daily_uses_count == 1) {
            Redis::expire($daily_usages_record_namespace, $second_left);
        }
        app()->make(ActionRewardDispatcher::class)->run('daily_usage', $partner, $partner, $portal_name);
    }

    public function getV3(Request $request)
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            /** @var Resource $resource */
            $resource = $request->manager_resource;
            $data     = (new PartnerRepository($partner))->getDashboard($resource);
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getFeatureVideos(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'sometimes|required|in:payment_link,pos,inventory,referral,due',
            ]);
            $repository=(new PartnerRepository($request->partner));
            $videos = $repository->featureVideos($request->type);
            return api_response($request, $videos, 200, ['feature_videos' => $videos]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param Setting $setting
     * @return JsonResponse
     */
    public function getHomeSetting(Request $request, Setting $setting)
    {
        try {
            $this->setModifier($request->partner);
            $setting = $setting->setPartner($request->partner)->get();
            return api_response($request, null, 200, ['data' => $setting]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param SettingV3 $setting
     * @return JsonResponse
     */
    public function getHomeSettingV3(Request $request, SettingV3 $setting)
    {
        try {
            $this->setModifier($request->partner);
            $home_page_setting = $setting->setPartner($request->partner)->get();
            foreach ($home_page_setting as &$setting) {
                if (is_object($setting)) {
                    in_array($setting->key, NewFeatures::get()) ? $setting->is_new = 1 : $setting->is_new = 0;
                } else {
                    in_array($setting['key'], NewFeatures::get()) ? $setting['is_new'] = 1 : $setting['is_new'] = 0;
                }
            }
            return api_response($request, null, 200, ['data' => $home_page_setting]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param PartnerRepositoryInterface $partner_repo
     * @param CacheManager $cache_manager
     * @return JsonResponse
     */
    public function updateHomeSetting(Request $request, PartnerRepositoryInterface $partner_repo, CacheManager $cache_manager)
    {
        try {
            $home_page_setting         = $request->home_page_setting;
            $data['home_page_setting'] = $home_page_setting;
            $partner_repo->update($request->partner, $data);
            $cache_manager->setPartner($request->partner)->store(json_decode($data['home_page_setting'], true));
            return api_response($request, null, 200, [
                'message' => 'Dashboard Setting updated successfully',
                'data'    => json_decode($home_page_setting)
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param PartnerRepositoryInterface $partner_repo
     * @return JsonResponse
     */
    public function updateHomeSettingV3(Request $request, PartnerRepositoryInterface $partner_repo)
    {
        try {
            $home_page_setting         = $request->home_page_setting;
            $data['home_page_setting_new'] = $home_page_setting;
            $partner_repo->update($request->partner, $data);
            return api_response($request, null, 200, [
                'message' => 'Dashboard Setting updated successfully',
                'data'    => json_decode($home_page_setting)
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function isUpdatedHomeSetting(Request $request)
    {
        try {
            $this->validate($request, [
                'last_updated' => 'sometimes|date|date_format:Y-m-d',
            ]);

            $is_updated = 1;
            $last_updated = DefaultSettingV3::getLastUpdatedAt();
            if($request->has('last_updated'))
            $is_updated = Carbon::parse($last_updated) > Carbon::parse($request->last_updated) ? 1 : 0;
            $data = [
                'is_updated' => $is_updated,
                'last_updated' => $last_updated
            ];

            return api_response($request, null, 200, ['data' => $data]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBkashNo(Request $request) {
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            $data = [
                'bkash_no'                     => $partner->bkash_no,
            ];
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getGeoInformation(Request $request) {
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            $data = [
                'geo_informations'  => json_decode($partner->geo_informations)
            ];
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getCurrentPackage(Request $request) {
        try {
            /** @var Partner $partner */
            $partner       = $request->partner;
            $data = [
                'current_subscription_package' => [
                    'id'            => $partner->subscription->id,
                    'name'          => $partner->subscription->name,
                    'name_bn'       => $partner->subscription->show_name_bn,
                    'remaining_day' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : 0,
                    'billing_type'  => $partner->billing_type,
                    'rules'         => $partner->subscription->getAccessRules(),
                    'is_light'      => $partner->subscription->id == (int)config('sheba.partner_lite_packages_id'),
                    'auto_billing_activated' => (bool)$partner->auto_billing_activated,
                    'subscription_renewal_warning' => (bool)$partner->subscription_renewal_warning,
                    'renewal_warning_days' => $partner->renewal_warning_days,
                ],
                "status" => $partner->getStatusToCalculateAccess()
            ];
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
