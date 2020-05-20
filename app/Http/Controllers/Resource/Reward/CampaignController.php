<?php namespace App\Http\Controllers\Resource\Reward;


use App\Http\Controllers\Controller;
use App\Models\RewardCampaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function show(RewardCampaign $campaign, Request $request)
    {
        $info = [
            "id" => $campaign->id,
            "name" => $campaign->reward->name,
            "short_description" => $campaign->reward->short_description,
            "type" => $campaign->reward->type,
            "amount" => $campaign->reward->amount,
            "start_time" => $campaign->reward->start_time->toDateTimeString(),
            "end_time" => $campaign->reward->end_time->toDateTimeString(),
            "created_at" => $campaign->reward->created_at->toDateTimeString(),
            "progress" => [
                "is_completed" => 0,
                "target" => 5,
                "completed" => 2
            ],
            "rules" => [
                "নুন্যতম ১০ টি অর্ডার সিডিউল ডিউ ছাড়া সার্ভ করতে হবে",
                "শুধু মাত্র সার্ভ অর্ডার গণনার আন্তর্ভুক্ত হবে",
                "বিজয়ী গন আগামী ৯ অক্টোবর রাত ১২ টার পরে তাদের গিফ্‌ট পয়েন্ট বুঝে পাবেন।"
            ]
        ];
        return api_response($request, null, 200, ['info' => $info]);
    }
}