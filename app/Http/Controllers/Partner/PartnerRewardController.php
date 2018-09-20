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

class PartnerRewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $campaigns = $point_actions = $credit_actions = array();
            $rewards = Reward::upcoming()->forPartner()->with('constraints')->get();
            $today = Carbon::today();
            foreach ($rewards as $reward) {
                if (!$this->isValidReward($partner, $reward)) continue;
                else {
                    $reward['days_left'] = $reward->end_time->diffInDays($today);
                    removeRelationsAndFields($reward, ['target_type']);
                    if ($reward->isCampaign()) array_push($campaigns, removeSelectedFieldsFromModel($reward, ['detail_type']));
                    elseif ($reward->isAction() && $reward->type == 'Point') array_push($point_actions, removeSelectedFieldsFromModel($reward, ['detail_type']));
                    else array_push($credit_actions, removeSelectedFieldsFromModel($reward, ['detail_type']));
                }
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

    private function isValidReward(Partner $partner, $reward)
    {
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

            $reward_logs = $reward_logs->orderBy('id', 'desc')->get();

            return api_response($request, null, 200, ['reward_history' => $reward_logs, 'gift_points' => $request->partner->reward_point]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getFaqs(Request $request)
    {
        try {
            $faqs = array(
                array(
                    'question' => 'গিফ্‌ট পয়েন্ট কি?',
                    'answer' => 'সময়মত উৎকৃষ্ট মানের সার্ভিস প্রদান করে পেতে পারেন পুরস্কার, তাই হচ্ছে গিফ্‌ট পয়েন্ট।'
                ),
                array(
                    'question' => 'কিভাবে গিফ্‌ট পয়েন্ট পাবেন?',
                    'answer' => 'সার্ভিস এর মান উন্নয়নের জন্য বিভিন্ন সময়ে টার্গেট প্রদান করা হবে, টার্গেট পূরণ করে পাবেন গিফ্‌ট পয়েন্ট। '
                ),
                array(
                    'question' => 'কিভাবে গিফ্‌ট পয়েন্ট ব্যবহার করবেন?',
                    'answer' => 'ম্যানেজার অ্যাপ এর গিফ্‌ট সপ এ যেসকল পন্য দেখতে পাবেন সেগুলো সংগ্রহ করতে গিফ্‌ট পয়েন্ট ব্যবহার করবেন। '
                )
            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}