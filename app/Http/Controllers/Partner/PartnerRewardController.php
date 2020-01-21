<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Reward;
use App\Models\RewardLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Reward\CampaignEventInitiator;
use Sheba\Reward\EventInitiator;
use Sheba\Reward\PartnerReward;

class PartnerRewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $campaigns = $point_actions = $credit_actions = array();
            $rewards = (new PartnerReward($partner))->upcoming();
            $today = Carbon::today();
            foreach ($rewards as $reward) {
                $reward['days_left'] = $reward->end_time->diffInDays($today);
                removeRelationsAndFields($reward, ['target_type']);
                if ($reward->isCampaign()) array_push($campaigns, removeSelectedFieldsFromModel($reward, ['detail_type']));
                elseif ($reward->isAction() && $reward->type == 'Point') array_push($point_actions, removeSelectedFieldsFromModel($reward, ['detail_type']));
                else array_push($credit_actions, removeSelectedFieldsFromModel($reward, ['detail_type']));
            }
            return api_response($request, $rewards, 200, ['campaigns' => $campaigns, 'actions' => array('point' => $point_actions, 'credit' => $credit_actions)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function show($partner, $reward, Request $request, CampaignEventInitiator $event_initiator)
    {
        try {
            $partner = $request->partner;
            $reward = Reward::with('detail')->find($reward);
            if ($reward->isAction()) return api_response($request, null, 404, ['message' => 'This is a action reward. No details available']);
            $events = [];
            foreach (json_decode($reward->detail->events) as $key => $event) {
                $event = $event_initiator->setReward($reward)->setName($key)->setRule($event)->initiate();
                $target_progress = $event->checkProgress($partner);
                array_push($events, array(
                    'tag' => $key,
                    'target' => $target_progress->getTarget(),
                    'completed' => $target_progress->getAchieved()
                ));
            }
            return api_response($request, $reward, 200, ['info' => $events]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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

            $reward_logs = $reward_logs->orderBy('id', 'desc')->get();

            return api_response($request, null, 200, ['reward_history' => $reward_logs, 'gift_points' => $request->partner->reward_point]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getFaqs(Request $request)
    {
        try {
            $faqs = [
                [
                    'question' => 'গিফ্‌ট পয়েন্ট কি?',
                    'answer' => 'সময়মত উৎকৃষ্ট মানের সার্ভিস প্রদান করে পেতে পারেন পুরস্কার, তাই হচ্ছে গিফ্‌ট পয়েন্ট।'
                ],
                [
                    'question' => 'কিভাবে গিফ্‌ট পয়েন্ট পাবেন?',
                    'answer' => 'সার্ভিস এর মান উন্নয়নের জন্য বিভিন্ন সময়ে টার্গেট প্রদান করা হবে, টার্গেট পূরণ করে পাবেন গিফ্‌ট পয়েন্ট। '
                ],
                [
                    'question' => 'কিভাবে গিফ্‌ট পয়েন্ট ব্যবহার করবেন?',
                    'answer' => 'ম্যানেজার অ্যাপ এর গিফ্‌ট সপ এ যেসকল পন্য দেখতে পাবেন সেগুলো সংগ্রহ করতে গিফ্‌ট পয়েন্ট ব্যবহার করবেন। '
                ],
                [
                    'question' => 'সেবা কি গিফট পয়েন্ট অথবা গিফট অর্ডার বাতিল করার অধিকার রাখে?',
                    'answer' => 'যদি কোন ইউজার অসৎ উপায় অবলম্বন করে গিফট পয়েন্ট অর্জন করে তবে সেই ইউজারের সকল গিফট অথবা গিফট অর্ডার বাতিল করার অধিকার সেবা রাখে।'
                ]
            ];
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReferralFaqs(Request $request){
        try{

            $faqs = array(
                array(
                    'question' => 'রেফারেল কী?',
                    'answer' => array('রেফারেল হলো sManager অ্যাপ ব্যবহার করে আয় করার একটি পদ্ধতি। যেখানে আপনি আপনার বন্ধুকে sManager অ্যাপটি ব্যবহারে সহযোগিতা করবেন।')
                ),
                array(
                    'question' => 'রেফার করে কিভাবে আয় করবো?',
                    'answer' => array('আপনার বন্ধু যদি আপনার পাঠানো রেফারেল লিংক থেকে sManager অ্যাপটি ডাউনলোড করে এবং ব্যবহার শুরু করে তাহলেই আপনার আয় শুরু হয়ে যাবে। অ্যাপটি ব্যবহারে ১ম থেকে ৫ম ধাপ পর্যন্ত প্রতিটি ধাপে আপনি আপনার সেবা ক্রেডিট ব্যালেন্সে টাকা পেতে থাকবেন। সর্বোচ্চ একটি সফল রেফারেল থেকে ৫০০ টাকা পর্যন্ত আয়ের সুযোগ রয়েছে।')
                ),
                array(
                    'question' => 'কিভাবে রেফার করবো?',
                    'answer' => array('আপনার sManager অ্যাপ-এ ঢুকে বাম পাশের ম্যানেজার টুলস-এ যান। রেফার করুন অপশনে ঢুকে লিংকটি ফোনবুক কিংবা বিভিন্ন মাধ্যমে শেয়ার করতে পারবেন।')
                ),
                array(
                    'question' => 'আয় করার ধাপ গুলো কী কী এবং কোন ধাপে কত টাকা পাবো?',
                    'answer' => array(
                        array('আয়ের মোট ৫ টি ধাপ রয়েছে।'),
                        array('১ম ধাপে আপনার রেফার করা বন্ধু যদি sManager অ্যাপটি ৬ দিন ব্যবহার করে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন ১০০ টাকা।'),
                        array('২য় ধাপে আপনার রেফার করা বন্ধু যদি sManager অ্যাপটি মোট ১২ দিন ব্যবহার করে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৩য় ধাপে আপনার রেফার করা বন্ধুকে sManager অ্যাপটি মোট ২৫ দিন ব্যবহার করতে হবে তাহলে  আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৪র্থ ধাপে আপনার রেফার করা বন্ধুকে sManager অ্যাপটি মোট ৫০ দিন ব্যবহার করতে হবে তাহলে  আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৫ম ধাপে আপনার বন্ধুকে sManager অ্যাপ-এর মাধ্যমে NID ভেরিফিকেশন করতে হবে, ভেরিফিকেশন করা হলে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('এভাবে রেফারেলের মাধ্যমে ধাপে ধাপে সর্বমোট ৫০০ টাকা পর্যন্ত আয় করতে পারবেন।')
                    )
                ),
                array(
                    'question' => 'রেফারেল-এর টাকা সেবা ক্রেডিট থেকে কিভাবে উত্তোলন করবো?',
                    'answer' => array('রেফারেল-এর টাকা sManager সেবা ক্রেডিট ব্যালেন্স থেকে আপনি যেকোনো সময় বিকাশ অথবা ব্যাংকের মাধ্যমে উত্তোলন করতে পারবেন।')
                )

            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);

        }catch (\Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReferralSteps(Request $request){
        try{
            $stepDetails = collect(config('partner.referral_steps'))
                ->map(function($item) {
                    return [
                        'ধাপ' => $item['step'],
                        'আপনার আয়' => $item['amount'],
                        'কিভাবে করবেন' => $item['details']
                    ];
                });
            return api_response($request, $stepDetails, 200, ['steps' => $stepDetails]);

        }catch (\Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
