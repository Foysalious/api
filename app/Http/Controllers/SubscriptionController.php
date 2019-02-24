<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        try{
            $categories = Category::whereNotNull('parent_id')->whereHas('services', function($q) {
                $q->whereHas('serviceSubscription',function($query) {
                    return $query->whereNotNull('id');
                });
            })->with(['services'=>function($q){
                $q->whereHas('serviceSubscription',function($query) {
                    return $query->whereNotNull('id');
                });
                $q->with('serviceSubscription');
            }])->get();

            $parents = collect();
            foreach ($categories as $category) {
                $parent =[
                    'id'=>$category->parent->id,
                    'name'=> $category->parent->name,
                    'bn_name' => $category->parent->bn_name,
                    'slug' => $category->parent->slug,
                    'short_description' => $category->parent->slug,
                    'subscriptions' =>  $category->services->map(function($service){
                        $service = removeRelationsAndFields($service);
                        list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                        $subscription = $service->serviceSubscription;
                        $subscription = removeRelationsAndFields($subscription);
                        $subscription['max_price'] = $service['max_price'];
                        $subscription['min_price'] = $service['min_price'];
                        $subscription['thumb'] = $service['thumb'];
                        $subscription['banner'] = $service['banner'];
                        return $subscription;
                    }),
                ];
                $parents->push($parent);
            }
            return api_response($request, $parents, 200, ['category' => $parents]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function all(Request $request)
    {
        try{
            $subscriptions = ServiceSubscription::all();
            foreach ($subscriptions as $subscription) {
                $service = removeRelationsAndFields($subscription->service);
                list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                $subscription = removeRelationsAndFields($subscription);
                $subscription['max_price'] = $service['max_price'];
                $subscription['min_price'] = $service['min_price'];
                $subscription['thumb'] = $service['thumb'];
                $subscription['banner'] = $service['banner'];
            }
            return api_response($request, $subscriptions, 200, ['subscriptions' => $subscriptions]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function all(Request $request)
    {
        try{
            $subscriptions = ServiceSubscription::all();
            foreach ($subscriptions as $subscription) {
                $service = removeRelationsAndFields($subscription->service);
                list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                $subscription = removeRelationsAndFields($subscription);
                $subscription['max_price'] = $service['max_price'];
                $subscription['min_price'] = $service['min_price'];
                $subscription['thumb'] = $service['thumb'];
                $subscription['banner'] = $service['banner'];
            }
            return api_response($request, $subscriptions, 200, ['subscriptions' => $subscriptions]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($serviceSubscription, Request $request)
    {
        try{
            $serviceSubscription = ServiceSubscription::find((int) $serviceSubscription);
            $options = $this->serviceQuestionSet($serviceSubscription->service);
            $serviceSubscription['questions'] = $options;
            list($service['max_price'], $service['min_price']) = $this->getPriceRange($serviceSubscription->service);
            $serviceSubscription['min_price'] = $service['min_price'];
            $serviceSubscription['max_price'] = $service['max_price'];
            $serviceSubscription['thumb'] = $serviceSubscription->service['thumb'];
            $serviceSubscription['banner'] = $serviceSubscription->service['banner'];
            removeRelationsAndFields($serviceSubscription);
            return api_response($request, $serviceSubscription, 200, ['details' => $serviceSubscription]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function serviceQuestionSet($service)
    {
        $questions = null;
        if ($service->variable_type == 'Options') {
            $questions = json_decode($service->variables)->options;
            foreach ($questions as &$question) {
                $question = collect($question);
                $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                $explode_answers = explode(',', $question->get('answers'));
                $question->put('answers', $explode_answers);
            }
            if (count($questions) == 1) {
                $questions[0]->put('input_type', 'selectbox');
            }
        }
        return $questions;
    }

    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }

    private function getPriceRange(Service $service)
    {
        try {
            $max_price = [];
            $min_price = [];
            if ($service->partners->count() == 0) return array(0, 0);
            foreach ($service->partners->where('status', 'Verified') as $partner) {
                $partner_service = $partner->pivot;
                if (!($partner_service->is_verified && $partner_service->is_published)) continue;
                $prices = (array)json_decode($partner_service->prices);
                $max = max($prices);
                $min = min($prices);
                array_push($max_price, $max);
                array_push($min_price, $min);
            }
            return array((double)max($max_price) * $service->min_quantity, (double)min($min_price) * $service->min_quantity);
        } catch (\Throwable $e) {
            return array(0, 0);
        }
    }
}
