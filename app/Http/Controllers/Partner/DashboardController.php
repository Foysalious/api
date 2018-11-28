<?php namespace App\Http\Controllers\Partner;

use App\Repositories\ReviewRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\Helpers\TimeFrame;

class DashboardController extends Controller
{
    public function get(Request $request, PartnerPerformance $performance)
    {
        $performance->setPartner($request->partner)->setTimeFrame((new TimeFrame())->forCurrentWeek())->calculate();
        $performanceStats = $performance->getData();

        $rating = (new ReviewRepository)->getAvgRating($request->partner->reviews);
        $rating = (string) (is_null($rating) ? 0 : $rating);

        $successful_jobs = $request->partner->notCancelledJobs();
        $sales_stats = (new PartnerSalesStatistics($request->partner))->calculate();

        $dashboard = [
            'name' => $request->partner->name,
            'logo' => $request->partner->logo,
            'current_subscription_bn' => $request->partner->subscription->tagline_bn,
            'badge' => $request->partner->subscription->badge_thumb,
            'rating' => $rating,
            'status' => $request->partner->status,
            'balance' => $request->partner->totalWalletAmount(),
            'credit' => $request->partner->wallet,
            'bonus_credit' => $request->partner->bonusWallet(),
            'reward_point' => $request->partner->reward_point,
            'bkash_no' => $request->partner->bkash_no,
            'current_stats' => [
                'total_order' => $request->partner->orders()->count(),
                'today_order' => $request->partner->todayJobs($successful_jobs)->count(),
                'tomorrow_order' =>  $request->partner->tomorrowJobs($successful_jobs)->count(),
                'not_responded' => $request->partner->notRespondedJobs($successful_jobs)->count(),
                'schedule_due' => $request->partner->scheduleDueJobs($successful_jobs)->count(),
                'complain' => $request->partner->complains()->count()
            ],
            'sales' => [
                'today' => [
                    'timeline' => date("jS F", strtotime(Carbon::today())),
                    'amount' =>  $sales_stats->today->sale
                ],
                'week' => [
                    'timeline' =>  date("jS F", strtotime(Carbon::today()->startOfWeek()))."-".date("jS F", strtotime(Carbon::today())),
                    'amount' => $sales_stats->week->sale
                ],
                'month' => [
                    'timeline' => date("jS F", strtotime(Carbon::today()->startOfMonth()))."-".date("jS F", strtotime(Carbon::today())),
                    'amount' => $sales_stats->month->sale
                ]
            ],
            'weekly_performance' => [
                'timeline' => date("jS F", strtotime(Carbon::today()->startOfWeek()))."-".date("jS F", strtotime(Carbon::today())),
                'successfully_completed' => [
                    'count' => $performanceStats['completed']['total'],
                    'performance' =>  $performanceStats['completed']['rate'],
                    'is_improved' =>  $performanceStats['completed']['is_improved']
                ],
                'completed_without_complain' => [
                    'count' => $performanceStats['no_complain']['total'],
                    'performance' =>  $performanceStats['no_complain']['rate'],
                    'is_improved' =>  $performanceStats['no_complain']['is_improved']
                ],
                'timely_accepted' => [
                    'count' => $performanceStats['timely_accepted']['total'],
                    'performance' =>  $performanceStats['timely_accepted']['rate'],
                    'is_improved' =>  $performanceStats['timely_accepted']['is_improved']
                ],
                'timely_started' => [
                    'count' => $performanceStats['timely_processed']['total'],
                    'performance' =>  $performanceStats['timely_processed']['rate'],
                    'is_improved' =>  $performanceStats['timely_processed']['is_improved']
                ]
            ],
            'subscription_promotion' => [
                'package' => $request->partner->subscription->tagline_bn,
                'package_badge' => $request->partner->subscription->badge,
                'package_usp_bn' => json_decode($request->partner->subscription->usps, 1)['usp_bn']
            ]
        ];
        return api_response($request, $dashboard, 200, ['data' => $dashboard]);
    }
}
