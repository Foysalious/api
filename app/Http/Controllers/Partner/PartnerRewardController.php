<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Reward;
use App\Models\RewardLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PartnerRewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $rewards = Reward::with('constraints')
                ->where([['end_time', '>=', Carbon::yesterday()], ['target_type', 'App\Models\Partner']])
                ->whereIn('detail_type', ['App\Models\RewardCampaign', 'App\Models\RewardAction'])
                ->get();
            $campaigns = $point_actions = $credit_actions = array();
            foreach ($rewards as $reward) {
                if (!$this->isValidReward($partner, $reward)) continue;
                else {
                    $reward['days_left'] = $reward->end_time->diffInDays(Carbon::today());
                    removeRelationsAndFields($reward);
                    if ($reward->detail_type == 'App\Models\RewardCampaign') array_push($campaigns, $reward);
                    elseif ($reward->detail_type == 'App\Models\RewardAction') {
                        $reward->type == 'Point' ? array_push($point_actions, $reward) : array_push($credit_actions, $reward);
                    }
                }
            }
            return api_response($request, $rewards, 200, ['campaigns' => $campaigns, 'actions' => array('point' => $point_actions, 'credit' => $credit_actions)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($partner, $reward, Request $request)
    {
        try {
            $partner = $request->partner;
            $reward = Reward::find($reward);
            return api_response($request, $reward, 200, ['info' => array(
                'target' => 50,
                'completed' => 25
            )]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function isValidReward(Partner $partner, $reward)
    {
        $category_pass = $package_pass = true;
        $category_constraints = $reward->constraints->where('constraint_type', constants('REWARD_CONSTRAINTS')['category']);
        $category_pass = $category_constraints->count() == 0;

        $package_constraints = $reward->constraints->where('constraint_type', constants('REWARD_CONSTRAINTS')['partner_package']);
        $package_pass = $package_constraints->count() == 0;

        if (!$category_pass) $category_pass = $this->checkForCategory($partner, $category_constraints);
        if (!$package_pass) $package_pass = $this->checkForPackage($partner, $package_constraints);
        return $category_pass && $package_pass;
    }

    private function checkForCategory(Partner $partner, $category_constraints)
    {
        $partner->load(['categories' => function ($q) {
            $q->where('categories.publication_status', 1)->wherePivot('is_verified', 1);
        }]);
        $partner_categories = $partner->categories->pluck('id')->unique()->toArray();
        foreach ($category_constraints as $category_constraint) {
            if (in_array($category_constraint->constraint_id, $partner_categories)) return true;
        }
        return false;
    }

    private function checkForPackage(Partner $partner, $package_constraints)
    {
        foreach ($package_constraints as $package_constraint) {
            if ($partner->package_id == $package_constraint->constraint_id) return true;
        }
        return false;
    }

    public function history(Request $request)
    {
        try {
            $start_date = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now();
            $end_date = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
            $reward_type = $request->has('reward_type') ? [ucfirst($request->reward_type)] : constants('REWARD_TYPE');

            $reward_logs = RewardLog::whereHas('reward', function ($query) use ($reward_type) {
                return $query->whereIn('type', $reward_type);
            })
                ->with(['reward' => function ($query) {
                    return $query->select('id', 'name', 'type', 'amount');
                }])
                ->forPartner($request->partner->id)
                ->rewardedAt([$start_date->startOfDay(), $end_date->endOfDay()])
                ->select('id', 'reward_id', 'log', 'created_at');

            if ($request->has('transaction_type')) {
                $reward_logs = $reward_logs->where('transaction_type', $request->transaction_type);
            }
            $reward_logs = $reward_logs->orderBy('id', 'desc')->get();

            return api_response($request, null, 200, ['reward_history' => $reward_logs, 'gift_points' => $request->partner->reward_point]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}