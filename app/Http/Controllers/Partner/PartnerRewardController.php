<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardCampaign;
use App\Models\RewardLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PartnerRewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $campaigns = Reward::with(['constraints', 'noConstraints'])
                ->where('end_time', '>=', Carbon::yesterday())
                ->where('detail_type', 'App\Models\RewardCampaign')->get();
            $actions = Reward::with(['constraints', 'noConstraints'])
                ->where('end_time', '>=', Carbon::yesterday())
                ->where('detail_type', 'App\Models\RewardAction')->get();
            foreach ($campaigns as $campaign) {
                $campaign['days_left'] = $campaign->end_time->diffInDays(Carbon::today());
                removeSelectedFieldsFromModel($campaign);
            }
            foreach ($actions as $action) {
                removeSelectedFieldsFromModel($action);
            }
            $actions = array(
                'point' => $actions->where('type', 'Point')->values()->all(),
                'cash' => $actions->where('type', 'Cash')->values()->all(),
            );
            return api_response($request, $campaigns, 200, ['campaigns' => $campaigns, 'actions' => $actions]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $start_date = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now();
            $end_date = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
            $reward_type = $request->has('reward_type') ? [ucfirst($request->reward_type)] : constants('REWARD_TYPE');

            $reward_logs = RewardLog::whereHas('reward', function($query) use ($reward_type) {
                    return $query->whereIn('type', $reward_type);
                })
                ->with(['reward' => function($query) {
                    return $query->select('id', 'name', 'type', 'amount');
                }])
                ->forPartner($request->partner->id)
                ->rewardedAt([$start_date->startOfDay(), $end_date->endOfDay()])
                ->select('id', 'reward_id', 'log', 'created_at');

            if ($request->has('transaction_type')) {
                $reward_logs = $reward_logs->where('transaction_type', $request->transaction_type);
            }
            $reward_logs = $reward_logs->get();

            return api_response($request, null, 200, ['reward_history' => $reward_logs, 'gift_points' => $request->partner->reward_point]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}