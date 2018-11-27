<?php namespace App\Http\Controllers\Partner;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function get(Request $request)
    {
        $dashboard = [
            'name' => $request->partner->name,
            'logo' => $request->partner->logo,
            'current_subscription_bn' => $request->partner->subscription->tagline_bn,
            'badge' => $request->partner->subscription->badge,
            'rating' => "4.00",
            'status' => 'active',
            'balance' => 1234,
            'credit' => 123,
            'bonus_credit' => 123,
            'reward_point' => 123,
            'inbox' => 4,
            'current_stats' => [
                'total_order' => 8,
                'today_order' => 3,
                'tomorrow_order' => 4,
                'not_responded' => 2,
                'schedule_due' => 2,
                'complain' => 2
            ],
            'sales' => [
                'today' => [
                    'timeline' => '31st October',
                    'amount' => 9056
                ],
                'week' => [
                    'timeline' => '27 Oct - 02 Nov',
                    'amount' => 65900
                ],
                'month' => [
                    'timeline' => 'October',
                    'amount' => 932879
                ]
            ],
            'weekly_performance' => [
                'timeline' => '26th October - 1st November',
                'successfully_completed' => [
                    'count' => 24,
                    'performance' => 49,
                    'is_improved' => 1
                ],
                'completed_without_complain' => [
                    'count' => 30,
                    'performance' => 60,
                    'is_improved' => 0
                ],
                'timely_accepted' => [
                    'count' => 46,
                    'performance' => 93,
                    'is_improved' => 0
                ],
                'timely_started' => [
                    'count' => 15,
                    'performance' => 30,
                    'is_improved' => 1
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

    public function weeklyPerformance(Request $request)
    {
        $performance = [
            'timeline' => 'Oct 26 - Nov 1',
            'performance_summary' => [
                'total_order_taken' => 51,
                'successfully_completed' => 39,
                'order_without_complain' => 30,
                'timely_order_taken' => 46,
                'timely_job_start' => 15
            ],
            'successfully_completed' => [
                'total_order' => 24,
                'rate' => 49,
                'last_week_rate' => 34,
                'is_improved' => 1,
                'last_week_rate_difference' => 15
            ],
            'order_without_complain' => [
                'total_order' => 30,
                'rate' => 60,
                'last_week_rate' => 54,
                'is_improved' => 1,
                'last_week_rate_difference' => 6
            ],
            'timely_order_taken' => [
                'total_order' => 46,
                'rate' => 93,
                'last_week_rate' => 95,
                'is_improved' => 0,
                'last_week_rate_difference' => 2
            ],
            'timely_job_start' => [
                'total_order' => 15,
                'rate' => 30,
                'last_week_rate' => 47,
                'is_improved' => 0,
                'last_week_rate_difference' => 17
            ]
        ];
        return api_response($request, $performance, 200, ['data' => $performance]);
    }
}
