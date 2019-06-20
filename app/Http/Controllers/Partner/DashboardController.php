<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\PartnerOrderController;
use App\Http\Controllers\Pos\OrderController;
use App\Models\PosOrder;
use phpDocumentor\Reflection\Types\This;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use App\Http\Controllers\SpLoanInformationCompletion;
use Sheba\Subscription\Partner\PartnerSubscriber;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\Analysis\PartnerSale\PartnerSale;
use App\Repositories\ReviewRepository;
use App\Http\Controllers\Controller;
use Sheba\Reward\PartnerReward;
use Sheba\Partner\LeaveStatus;
use App\Models\SliderPortal;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;
use Sheba\Manager\JobList;
use GuzzleHttp\Client;
use App\Models\Slider;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function get(Request $request, PartnerPerformance $performance, PartnerReward $partner_reward)
    {
        try {
            ini_set('memory_limit', '6096M');
            $partner = $request->partner;
            /*$pos_order_dues = 0;
            $pos_orders = $partner->posOrders->map(function ($pos_order) use ($pos_order_dues) {
                dd($pos_order);
            });

            dd($pos_order_dues);*/
            $slider_portal = SliderPortal::with('slider.slides')
                ->where('portal_name', 'manager-app')
                ->where('screen', 'home')
                ->get();
            $slide = $slider_portal->last()->slider->slides->last();
            $performance->setPartner($partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
            $performanceStats = $performance->getData();

            $rating = (new ReviewRepository)->getAvgRating($partner->reviews);
            $rating = (string)(is_null($rating) ? 0 : $rating);
            $successful_jobs = $partner->notCancelledJobs();
            $sales_stats = (new PartnerSalesStatistics($partner))->calculate();
            $upgradable_package = (new PartnerSubscriber($partner))->getUpgradablePackage();
            $new_order = $this->newOrdersCount($partner, $request);
            $dashboard = [
                'name' => $partner->name,
                'logo' => $partner->logo,
                'geo_informations' => json_decode($partner->geo_informations),
                'current_subscription_package' => [
                    'name' => $partner->subscription->name,
                    'name_bn' => $partner->subscription->name_bn
                ],
                'badge' => $partner->resolveBadge(),
                'rating' => $rating,
                'status' => constants('PARTNER_STATUSES_SHOW')[$partner['status']]['partner'],
                'balance' => $partner->totalWalletAmount(),
                'credit' => $partner->wallet,
                'bonus' => round($partner->bonusWallet(), 2),
                'is_credit_limit_exceed' => $partner->isCreditLimitExceed(),
                'is_on_leave' => $partner->runningLeave() ? 1 : 0,
                'bonus_credit' => $partner->bonusWallet(),
                'reward_point' => $partner->reward_point,
                'bkash_no' => $partner->bkash_no,
                'current_stats' => [
                    'total_new_order' => $new_order->total_new_orders,
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
                    ]
                ],
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
                'manager_dashboard_banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/categories/24/app_banner.jpg',
                'video' => json_decode($slide->video_info),
                'has_pos_inventory' => $partner->posServices->isEmpty() ? 0 : 1,
                'has_kyc_profile_completed' => $this->getSpLoanInformationCompletion($partner, $request),
                'has_pos_due_order' => $this->posDueOrders($request),
                'has_pos_paid_order' => $this->posPaidOrders($request),
            ];

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

    private function posPaidOrders($request)
    {
        try {
            $request->merge(['status' => 'Paid']);
            $due_order = new OrderController();
            $due_order = $due_order->index($request)->getData();
            return count($due_order->orders) > 0 ? 1 : 0;
        } catch (\Throwable $e) {
            return array();
        }
    }

    private function posDueOrders($request)
    {
        try {
            $request->merge(['status' => 'Due']);
            $paid_order = new OrderController();
            $paid_order = $paid_order->index($request)->getData();
            return count($paid_order->orders) > 0 ? 1 : 0;
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
}
