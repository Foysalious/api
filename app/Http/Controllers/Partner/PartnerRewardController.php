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
            $partner=$request->partner;
            $campaigns = Reward::with(['constraints', 'detail', 'noConstraints', 'constraints'])
                ->where('end_time', '>=', Carbon::yesterday())
                ->where('detail_type', 'App\Models\RewardCampaign')->get();
            $actions = Reward::with(['constraints', 'noConstraints'])
                ->where('end_time', '>=', Carbon::yesterday())
                ->where('detail_type', 'App\Models\RewardAction')->get();
            foreach ($campaigns as $campaign) {
                $skip_types = [];
                foreach ($campaign->noConstraints as $noConstraint) {
                    array_push($skip_types, $noConstraint->constraint_type);
                }
                foreach ($campaign->constraints as $constraint) {
                    if (in_array($constraint->constraint_type, $skip_types)) continue;
                    else {
                        if ($constraint->constraint_type == "App\Models\Category") {
                        } elseif ($constraint->constraint_type == "App\Models\PartnerSubscriptionPackage") {
//                            if($partner->package_id==$constraint->constraint_id)
                        }
                    }
                }
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
}