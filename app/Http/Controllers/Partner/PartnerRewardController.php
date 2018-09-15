<?php

namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardCampaign;
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
            return api_response($request, $campaigns, 200, ['campaigns' => $campaigns, 'actions' => $actions]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}