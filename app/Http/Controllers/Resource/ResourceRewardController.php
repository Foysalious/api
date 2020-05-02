<?php namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ResourceRewardController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = [
            [
                "id" => 2,
                "name" => "সিডিউল মাস্টার",
                "short_description" => "সিডিউল ডিউ ছাড়া সার্ভ করলেই ১৫,০০০ গিফ্‌ট পয়েন্ট",
                "long_description" => "নুন্যতম ১০ টি অর্ডার সিডিউল ডিউ ছাড়া সার্ভ করতে হবে<br>শুধু মাত্র সার্ভ অর্ডার গণনার আন্তর্ভুক্ত হবে<br>বিজয়ী গন আগামী ৯ অক্টোবর রাত ১২ টার পরে তাদের গিফ্‌ট পয়েন্ট বুঝে পাবেন। ",
                "image" => null,
                "detail_id" => 1,
                "type" => "Point",
                "amount" => 15000,
                "is_amount_percentage" => 0,
                "cap" => null,
                "start_time" => "2018-10-03 00:00:00",
                "end_time" => "2018-10-09 23:59:59",
                "valid_till_date" => null,
                "valid_till_day" => null,
                "created_at" => "2018-10-03 11:47:36",
                "days_left" => 568,
                "progress" => [
                    "tag" => "order_serve",
                    "is_completed" => 0,
                    "target" => 5,
                    "completed" => 2
                ]
            ],
            [
                "id" => 3,
                "name" => "এক্সেপ্ট মাস্টার",
                "short_description" => "২ মিনিটের মধ্যে অর্ডার এক্সেপ্ট করলেই ১০,০০০ গিফ্‌ট পয়েন্ট",
                "long_description" => "অর্ডার আসার ২ মিনিটের মধ্যে আক্সেপ্ট করতে হবে<br>নুন্যতম ১০ টি অর্ডার আক্সেপ্ট করতে হবে<br>শুধু মাত্র সার্ভ অর্ডার গননার আন্তর্ভুক্ত হবে<br>বিজয়ী গন আগামী ৯ অক্টোবর রাত ১২ টার পরে তাদের গিফ্‌ট পয়েন্ট বুঝে পাবেন। ",
                "image" => null,
                "detail_id" => 2,
                "type" => "Point",
                "amount" => 10000,
                "is_amount_percentage" => 0,
                "cap" => null,
                "start_time" => "2018-10-03 00:00:00",
                "end_time" => "2018-10-09 23:59:59",
                "valid_till_date" => "2018-10-09 23:59:59",
                "valid_till_day" => null,
                "created_at" => "2018-10-03 11:49:59",
                "days_left" => 568,
                "progress" => [
                    "tag" => "order_accept",
                    "is_completed" => 0,
                    "target" => 5,
                    "completed" => 2
                ]
            ]
        ];
        $actions = [
            [
                "id" => 1,
                "name" => "৫ স্টার বোনাস",
                "short_description" => null,
                "long_description" => "অর্ডার সার্ভ করে ৫ স্টার পেলেই ১,০০০ পয়েন্ট\r\n৩১ অক্টোবর পর্যন্ত প্রযোজ্য",
                "image" => null,
                "detail_id" => 1,
                "type" => "Point",
                "amount" => 1000,
                "is_amount_percentage" => 0,
                "cap" => null,
                "start_time" => "2018-09-23 00:00:00",
                "end_time" => "2018-10-31 23:59:59",
                "valid_till_date" => null,
                "valid_till_day" => null,
                "created_at" => "2018-09-22 13:31:25",
                "days_left" => 546,
                "progress" => [
                    "tag" => "rating",
                    "is_completed" => 1,
                    "target" => null,
                    "completed" => null
                ]
            ],
            [
                "id" => 10,
                "name" => "রিচার্জ বোনাস",
                "short_description" => "রিচার্জ করলেই গিফট পয়েন্ট",
                "long_description" => "প্রতি ৫০০ টাকা রিচার্জ এ ১,০০০ গিফট পয়েন্ট",
                "image" => null,
                "detail_id" => 2,
                "type" => "Point",
                "amount" => 1000,
                "is_amount_percentage" => 0,
                "cap" => null,
                "start_time" => "2018-10-23 00:00:00",
                "end_time" => "2018-10-27 23:59:59",
                "valid_till_date" => null,
                "valid_till_day" => null,
                "created_at" => "2018-10-23 11:30:21",
                "days_left" => 550,
                "progress" => [
                    "tag" => "rating",
                    "is_completed" => 1,
                    "target" => null,
                    "completed" => null
                ]
            ]
        ];
        return api_response($request, null, 200, ['campaigns' => $campaigns, 'actions' => $actions]);
    }

    public function history(Request $request)
    {
        $history = [
            [
                "id" => 37594,
                "reward_id" => 36,
                "log" => "৫ টি সার্ভিসে ৫ স্টার",
                "created_at" => "2019-03-04 14:45:33",
                "reward" => [
                    "id" => 36,
                    "name" => "৫ স্টার বোনাস",
                    "type" => "Cash",
                    "detail_type" => 'Campaign',
                    "amount" => 100
                ],
                "progress" => [
                    "tag" => "rating",
                    "is_completed" => 0,
                    "target" => 5,
                    "completed" => 2
                ]
            ]
        ];
        return api_response($request, null, 200, ['reward_history' => $history]);
    }

    public function show($reward, Request $request)
    {
        $info = [
            "id" => 2,
            "name" => "সিডিউল মাস্টার",
            "short_description" => "সিডিউল ডিউ ছাড়া সার্ভ করলেই ১৫,০০০ গিফ্‌ট পয়েন্ট",
            "long_description" => "নুন্যতম ১০ টি অর্ডার সিডিউল ডিউ ছাড়া সার্ভ করতে হবে<br>শুধু মাত্র সার্ভ অর্ডার গণনার আন্তর্ভুক্ত হবে<br>বিজয়ী গন আগামী ৯ অক্টোবর রাত ১২ টার পরে তাদের গিফ্‌ট পয়েন্ট বুঝে পাবেন। ",
            "image" => null,
            "detail_id" => 1,
            "type" => "Point",
            "amount" => 15000,
            "is_amount_percentage" => 0,
            "cap" => null,
            "start_time" => "2018-10-03 00:00:00",
            "end_time" => "2018-10-09 23:59:59",
            "valid_till_date" => null,
            "valid_till_day" => null,
            "created_at" => "2018-10-03 11:47:36",
            "days_left" => 568,
            "progress" => [
                "tag" => "order_serve",
                "is_completed" => 0,
                "target" => 5,
                "completed" => 2
            ]
        ];
        return api_response($request, null, 200, ['info' => $info]);
    }
}
