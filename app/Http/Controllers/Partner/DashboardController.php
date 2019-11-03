<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PartnerOrderController;
use App\Http\Controllers\SpLoanInformationCompletion;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\SliderPortal;
use App\Repositories\ReviewRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\Helpers\TimeFrame;
use Sheba\Manager\JobList;
use Sheba\Partner\LeaveStatus;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Reward\PartnerReward;

class DashboardController extends Controller
{
    public function get(Request $request, PartnerPerformance $performance, PartnerReward $partner_reward)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 660);

        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            $slider_portal = SliderPortal::with('slider.slides')
                ->where('portal_name', 'manager-app')
                ->where('screen', 'home')
                ->get();
            $slide = !$slider_portal->isEmpty() ? $slider_portal->last()->slider->slides->last() : null;
            $performance->setPartner($partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
            $performanceStats = $performance->getData();

            $rating = (new ReviewRepository)->getAvgRating($partner->reviews);
            $rating = (string)(is_null($rating) ? 0 : $rating);
            $successful_jobs = $partner->notCancelledJobs();
            $sales_stats = (new PartnerSalesStatistics($partner))->calculate();
            $upgradable_package = null;
            $new_order = $this->newOrdersCount($partner, $request);

            $total_due_for_pos_orders = 0;
            $has_pos_paid_order = 0;
            PosOrder::with('items.service.discounts', 'customer', 'payments', 'logs', 'partner')->byPartner($partner->id)
                ->each(function (PosOrder $pos_order) use (&$total_due_for_pos_orders, &$has_pos_paid_order) {
                    $pos_order->calculate();
                    $due = $pos_order->getDue();
                    $total_due_for_pos_orders += $due > 0 ? $due : 0;
                    if (!$has_pos_paid_order && ($pos_order->getPaymentStatus() == OrderPaymentStatuses::PAID))
                        $has_pos_paid_order = 1;
                });

            $dashboard = [
                'name' => $partner->name,
                'logo' => $partner->logo,
                'geo_informations' => json_decode($partner->geo_informations),
                'current_subscription_package' => [
                    'id' => $partner->subscription->id,
                    'name' => $partner->subscription->name,
                    'name_bn' => $partner->subscription->show_name_bn,
                    'remaining_day' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : 0,
                    'billing_type' => $partner->billing_type,
                    'rules' => $partner->subscription->getAccessRules(),
                    'is_light' => $partner->subscription->id == (int)config('sheba.partner_lite_packages_id')
                ],
                'badge' => $partner->resolveBadge(),
                'rating' => $rating,
                'status' => $partner->getStatusToCalculateAccess(),
                'show_status' => constants('PARTNER_STATUSES_SHOW')[$partner['status']]['partner'],
                'balance' => $partner->totalWalletAmount(),
                'credit' => $partner->wallet,
                'bonus' => round($partner->bonusWallet(), 2),
                'is_credit_limit_exceed' => $partner->isCreditLimitExceed(),
                'is_on_leave' => $partner->runningLeave() ? 1 : 0,
                'bonus_credit' => $partner->bonusWallet(),
                'reward_point' => $partner->reward_point,
                'bkash_no' => $partner->bkash_no,
                'current_stats' => [
                    'total_new_order' => count($new_order) > 0 ? $new_order->total_new_orders : 0,
                    'total_order' => $partner->orders()->count(),
                    'total_ongoing_order' => (new JobList($partner))->ongoing()->count(),
                    'today_order' => $partner->todayJobs($successful_jobs)->count(),
                    'tomorrow_order' => $partner->tomorrowJobs($successful_jobs)->count(),
                    'not_responded' => $partner->notRespondedJobs($successful_jobs)->count(),
                    'schedule_due' => $partner->scheduleDueJobs($successful_jobs)->count(),
                    'serve_due' => $partner->serveDueJobs($successful_jobs)->count(),
                    'complain' => $partner->complains()->notClosed()->count()
                ],
                'sales' => [
                    'today' => [
                        'timeline' => date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->today->orderTotalPrice + $sales_stats->today->posSale
                    ],
                    'week' => [
                        'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->week->orderTotalPrice + $sales_stats->week->posSale
                    ],
                    'month' => [
                        'timeline' => date("jS F", strtotime(Carbon::today()->startOfMonth())) . "-" . date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->month->orderTotalPrice + $sales_stats->month->posSale
                    ],
                    'total_due_for_pos_orders' => $total_due_for_pos_orders,
                    #'total_due_for_sheba_orders' => $total_due_for_sheba_orders,
                ],
                'is_nid_verified' => (int)$request->manager_resource->profile->nid_verified ? true : false,
                'weekly_performance' => [
                    'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                    'successfully_completed' => [
                        'count' => $performanceStats['completed']['total'],
                        'performance' => $this->formatRate($performanceStats['completed']['rate']),
                        'is_improved' => $performanceStats['completed']['is_improved']
                    ],
                    'completed_without_complain' => [
                        'count' => $performanceStats['no_complain']['total'],
                        'performance' => $this->formatRate($performanceStats['no_complain']['rate']),
                        'is_improved' => $performanceStats['no_complain']['is_improved']
                    ],
                    'timely_accepted' => [
                        'count' => $performanceStats['timely_accepted']['total'],
                        'performance' => $this->formatRate($performanceStats['timely_accepted']['rate']),
                        'is_improved' => $performanceStats['timely_accepted']['is_improved']
                    ],
                    'timely_started' => [
                        'count' => $performanceStats['timely_processed']['total'],
                        'performance' => $this->formatRate($performanceStats['timely_processed']['rate']),
                        'is_improved' => $performanceStats['timely_processed']['is_improved']
                    ]
                ],
                'subscription_promotion' => $upgradable_package ? [
                    'package' => $upgradable_package->name,
                    'package_name_bn' => $upgradable_package->name_bn,
                    'package_badge' => $upgradable_package->badge,
                    'package_usp_bn' => json_decode($upgradable_package->usps, 1)['usp_bn']
                ] : null,
                'has_reward_campaign' => count($partner_reward->upcoming()) > 0 ? 1 : 0,
                'leave_info' => (new LeaveStatus($partner))->getCurrentStatus(),
                'sheba_order' => $partner->orders->isEmpty() ? 0 : 1,
                'manager_dashboard_banner' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner_assets/dashboard/manager_dashboard.png',
                'video' => $slide ? json_decode($slide->video_info) : null,
                'has_pos_inventory' => $partner->posServices->isEmpty() ? 0 : 1,
                'has_kyc_profile_completed' => $this->getSpLoanInformationCompletion($partner, $request),
                'has_pos_due_order' => $total_due_for_pos_orders > 0 ? 1 : 0,
                'has_pos_paid_order' => $has_pos_paid_order,
            ];

            if (request()->hasHeader('Portal-Name'))
                $this->setDailyUsageRecord($partner, request()->header('Portal-Name'));

            return api_response($request, $dashboard, 200, ['data' => $dashboard]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function newOrdersCount($partner, $request)
    {
        try {
            $request->merge(['getCount' => 1]);
            $partner_order = new PartnerOrderController();
            $new_order = $partner_order->newOrders($partner, $request)->getData();
            return $new_order;
        } catch (\Throwable $e) {
            return array();
        }
    }

    private function getSpLoanInformationCompletion($partner, $request)
    {
        try {
            $sp_loan_information_completion = new SpLoanInformationCompletion();
            $sp_information_completion = $sp_loan_information_completion->getLoanInformationCompletion($partner, $request)->getData()->completion;
            $personal = $sp_information_completion->personal->completion_percentage;
            $business = $sp_information_completion->business->completion_percentage;
            $finance = $sp_information_completion->finance->completion_percentage;
            $nominee = $sp_information_completion->nominee->completion_percentage;
            $documents = $sp_information_completion->documents->completion_percentage;
            return ($personal == 100 && $business == 100 && $finance == 100 && $nominee == 100 && $documents == 100) ? 1 : 0;
        } catch (\Throwable $e) {
            return array();
        }
    }

    private function formatRate($rate)
    {
        if ($rate < 0) return 0;
        if ($rate > 100) return 100;
        return $rate;
    }

    /**
     * @param Partner $partner
     * @param $portal_name
     */
    private function setDailyUsageRecord(Partner $partner, $portal_name)
    {
        $daily_usages_record_namespace = 'PartnerDailyAppUsages:partner_' . $partner->id;
        $daily_uses_count = Redis::get($daily_usages_record_namespace);
        $daily_uses_count = !is_null($daily_uses_count) ? (int)$daily_uses_count + 1 : 1;

        $second_left = Carbon::now()->diffInSeconds(Carbon::today()->endOfDay(), false);
        Redis::set($daily_usages_record_namespace, $daily_uses_count);

        if ($daily_uses_count == 1) {
            Redis::expire($daily_usages_record_namespace, $second_left);
        }

        app()->make(ActionRewardDispatcher::class)->run('daily_usage', $partner, $partner,$portal_name);
    }

    public function getHomeSetting(Request $request)
    {
        try {
            $setting = "[{\"key\":\"pos\",\"name_en\":\"Sales Point\",\"name_bn\":\"বেচা-বিক্রি\",\"is_on_homepage\":1},{\"key\":\"pos_due\",\"name_en\":\"Due Tracker\",\"name_bn\":\"বাকীর খাতা\",\"is_on_homepage\":1},{\"key\":\"payment_link\",\"name_en\":\"Digital Collection\",\"name_bn\":\"ডিজিটাল কালেকশন\",\"is_on_homepage\":1},{\"key\":\"online_sheba\",\"name_en\":\"Online Sheba\",\"name_bn\":\"অনলাইন বিক্রি\",\"is_on_homepage\":1},{\"key\":\"extra_income\",\"name_en\":\"Extra Income\",\"name_bn\":\"বাড়তি আয়\",\"is_on_homepage\":1},{\"key\":\"loan\",\"name_en\":\"Loan\",\"name_bn\":\"সহজ লোণ\",\"is_on_homepage\":1},{\"key\":\"earnings\",\"name_en\":\"Earnings\",\"name_bn\":\"ড্যাশবোর্ড\",\"is_on_homepage\":1},{\"key\":\"pos_history\",\"name_en\":\"Pos History\",\"name_bn\":\"বিক্রির খাতা\",\"is_on_homepage\":0},{\"key\":\"customer_list\",\"name_en\":\"Customer List\",\"name_bn\":\"গ্রাহক তালিকা\",\"is_on_homepage\":0},{\"key\":\"marketing\",\"name_en\":\"Marketing & Promo\",\"name_bn\":\"মার্কেটিং ও প্রোমো\",\"is_on_homepage\":0},{\"key\":\"report\",\"name_en\":\"Report\",\"name_bn\":\"রিপোর্ট\",\"is_on_homepage\":0},{\"key\":\"stock\",\"name_en\":\"Stock\",\"name_bn\":\"স্টক\",\"is_on_homepage\":0},{\"key\":\"e-shop\",\"name_en\":\"E-Shop\",\"name_bn\":\"পাইকারি বাজার\",\"is_on_homepage\":0},{\"key\":\"expense\",\"name_en\":\"Expense Track\",\"name_bn\":\"হিসাব খাতা\",\"is_on_homepage\":0},{\"key\":\"gift_shop\",\"name_en\":\"Gift Shop\",\"name_bn\":\"গিফট শপ\",\"is_on_homepage\":0}]";
            return api_response($request, null, 200, ['data' => json_decode($setting)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setHomeSetting(Request $request)
    {
        try {
            $setting = "[{\"key\":\"pos\",\"name_en\":\"Sales Point\",\"name_bn\":\"বেচা-বিক্রি\",\"is_on_homepage\":1},{\"key\":\"pos_due\",\"name_en\":\"Due Tracker\",\"name_bn\":\"বাকীর খাতা\",\"is_on_homepage\":1},{\"key\":\"payment_link\",\"name_en\":\"Digital Collection\",\"name_bn\":\"ডিজিটাল কালেকশন\",\"is_on_homepage\":1},{\"key\":\"online_sheba\",\"name_en\":\"Online Sheba\",\"name_bn\":\"অনলাইন বিক্রি\",\"is_on_homepage\":1},{\"key\":\"extra_income\",\"name_en\":\"Extra Income\",\"name_bn\":\"বাড়তি আয়\",\"is_on_homepage\":1},{\"key\":\"loan\",\"name_en\":\"Loan\",\"name_bn\":\"সহজ লোণ\",\"is_on_homepage\":1},{\"key\":\"earnings\",\"name_en\":\"Earnings\",\"name_bn\":\"ড্যাশবোর্ড\",\"is_on_homepage\":1},{\"key\":\"pos_history\",\"name_en\":\"Pos History\",\"name_bn\":\"বিক্রির খাতা\",\"is_on_homepage\":0},{\"key\":\"customer_list\",\"name_en\":\"Customer List\",\"name_bn\":\"গ্রাহক তালিকা\",\"is_on_homepage\":0},{\"key\":\"marketing\",\"name_en\":\"Marketing & Promo\",\"name_bn\":\"মার্কেটিং ও প্রোমো\",\"is_on_homepage\":0},{\"key\":\"report\",\"name_en\":\"Report\",\"name_bn\":\"রিপোর্ট\",\"is_on_homepage\":0},{\"key\":\"stock\",\"name_en\":\"Stock\",\"name_bn\":\"স্টক\",\"is_on_homepage\":0},{\"key\":\"e-shop\",\"name_en\":\"E-Shop\",\"name_bn\":\"পাইকারি বাজার\",\"is_on_homepage\":0},{\"key\":\"expense\",\"name_en\":\"Expense Track\",\"name_bn\":\"হিসাব খাতা\",\"is_on_homepage\":0},{\"key\":\"gift_shop\",\"name_en\":\"Gift Shop\",\"name_bn\":\"গিফট শপ\",\"is_on_homepage\":0}]";
            return api_response($request, null, 200, ['message'=>'Dashboard Setting updated successfully','data' => json_decode($setting)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

}
