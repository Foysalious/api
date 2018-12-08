<?php namespace App\Http\Controllers\Partner;

use App\Repositories\ReviewRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Analysis\PartnerSale\PartnerSale;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\Helpers\TimeFrame;
use Sheba\Reward\PartnerReward;
use Sheba\Subscription\Partner\PartnerSubscriber;

class DashboardController extends Controller
{
    public function get(Request $request, PartnerPerformance $performance, PartnerReward $partner_reward)
    {
        try {
            $partner = $request->partner;
            $performance->setPartner($partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
            $performanceStats = $performance->getData();

            $rating = (new ReviewRepository)->getAvgRating($partner->reviews);
            $rating = (string)(is_null($rating) ? 0 : $rating);
            $successful_jobs = $partner->notCancelledJobs();
            $sales_stats = (new PartnerSalesStatistics($partner))->calculate();
            $upgradable_package = (new PartnerSubscriber($partner))->getUpgradablePackage();
            $dashboard = [
                'name' => $partner->name,
                'logo' => $partner->logo,
                'geo_informations' => json_decode($partner->geo_informations),
                'current_subscription_package' => [
                    'name' => $partner->subscription->name,
                    'name_bn' => $partner->subscription->name_bn
                ],
                'badge' => $partner->subscription->badge_thumb,
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
                    'total_order' => $partner->orders()->count(),
                    'today_order' => $partner->todayJobs($successful_jobs)->count(),
                    'tomorrow_order' => $partner->tomorrowJobs($successful_jobs)->count(),
                    'not_responded' => $partner->notRespondedJobs($successful_jobs)->count(),
                    'schedule_due' => $partner->scheduleDueJobs($successful_jobs)->count(),
                    'complain' => $partner->complains()->count()
                ],
                'sales' => [
                    'today' => [
                        'timeline' => date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->today->orderTotalPrice
                    ],
                    'week' => [
                        'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->week->orderTotalPrice
                    ],
                    'month' => [
                        'timeline' => date("jS F", strtotime(Carbon::today()->startOfMonth())) . "-" . date("jS F", strtotime(Carbon::today())),
                        'amount' => $sales_stats->month->orderTotalPrice
                    ]
                ],
                'weekly_performance' => [
                    'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek())) . "-" . date("jS F", strtotime(Carbon::today())),
                    'successfully_completed' => [
                        'count' => $performanceStats['completed']['total'],
                        'performance' => $performanceStats['completed']['rate'],
                        'is_improved' => $performanceStats['completed']['is_improved']
                    ],
                    'completed_without_complain' => [
                        'count' => $performanceStats['no_complain']['total'],
                        'performance' => $performanceStats['no_complain']['rate'],
                        'is_improved' => $performanceStats['no_complain']['is_improved']
                    ],
                    'timely_accepted' => [
                        'count' => $performanceStats['timely_accepted']['total'],
                        'performance' => $performanceStats['timely_accepted']['rate'],
                        'is_improved' => $performanceStats['timely_accepted']['is_improved']
                    ],
                    'timely_started' => [
                        'count' => $performanceStats['timely_processed']['total'],
                        'performance' => $performanceStats['timely_processed']['rate'],
                        'is_improved' => $performanceStats['timely_processed']['is_improved']
                    ]
                ],
                'subscription_promotion' => $upgradable_package ? [
                    'package' => $upgradable_package->name,
                    'package_name_bn' => $upgradable_package->name_bn,
                    'package_badge' => $upgradable_package->badge,
                    'package_usp_bn' => json_decode($upgradable_package->usps, 1)['usp_bn']
                ] : null,
                'has_reward_campaign' => count($partner_reward->upcoming()) > 0 ? 1 : 0
            ];
            return api_response($request, $dashboard, 200, ['data' => $dashboard]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
