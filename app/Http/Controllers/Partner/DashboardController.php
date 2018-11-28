<?php namespace App\Http\Controllers\Partner;

use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function get(Request $request)
    {
        $rating = (new ReviewRepository)->getAvgRating($request->partner->reviews);
        $rating = (string) (is_null($rating) ? 0 : $rating);

        $dashboard = [
            'name' => $request->partner->name,
            'logo' => $request->partner->logo,
            'current_subscription_bn' => $request->partner->subscription->tagline_bn,
            'badge' => $request->partner->subscription->badge_thumb,
            'rating' => $rating,
            'status' => 'active',
            'balance' => $request->partner->totalWalletAmount(),
            'credit' => $request->partner->wallet,
            'bonus_credit' => $request->partner->bonusWallet(),
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
}
