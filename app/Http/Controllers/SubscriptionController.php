<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\Service;
use App\Models\ServiceSubscription;
use Illuminate\Http\Request;
use Sheba\Subscription\ApproximatePriceCalculator;

class SubscriptionController extends Controller
{
    public function index(Request $request, ApproximatePriceCalculator $approximatePriceCalculator)
    {
        try {
            if ($request->has('location')) {
                $location = $request->location != '' ? $request->location : 4;
            } else {
                if ($request->has('lat') && $request->has('lng')) {
                    $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                    if (!is_null($hyperLocation)) $location = $hyperLocation->location->id; else return api_response($request, null, 404);
                } else $location = 4;
            }

            $categories = Category::whereNotNull('parent_id')->whereHas('services', function ($q) {
                $q->whereHas('serviceSubscription', function ($query) {
                    return $query->whereNotNull('id');
                });
            })->with(['services' => function ($q) use ($location) {
                $q->whereHas('serviceSubscription', function ($query) {
                    return $query->whereNotNull('id');
                });
                $q->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location);
                });
                $q->with('serviceSubscription');
            }])->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location);
            })->get();

            $parents = collect();
            foreach ($categories as $category) {
                $subscriptions =  $category->services->map(function($service) use ($approximatePriceCalculator){
                    $service = removeRelationsAndFields($service);
                    list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                    $subscription = $service->serviceSubscription;
                    $price_range = $approximatePriceCalculator->setSubscription($subscription)->getPriceRange();
                    $subscription = removeRelationsAndFields($subscription);
                    $subscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
                    $subscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
                    $subscription['price_applicable_for'] = $price_range['price_applicable_for'];
                    $subscription['thumb'] = $service['thumb'];
                    $subscription['banner'] = $service['banner'];
                    return $subscription;
                });
                $parent =[
                    'id'=>$category->parent->id,
                    'name'=> $category->parent->name,
                    'bn_name' => $category->parent->bn_name,
                    'slug' => $category->parent->slug,
                    'short_description' => $category->parent->slug,
                    'subscriptions' => $subscriptions
                ];
                if(count($parent['subscriptions']) > 0) {
                    $existingParent = $parents->filter(function($parent) use ($category) {
                        if($parent['id'] === $category->parent->id) return $parent;
                    });
                    if(count($existingParent)>0) {
                        foreach($subscriptions as $subscription) {
                            $existingParent->first()['subscriptions']->push($subscription);
                        }
                    }
                    else
                        $parents->push($parent);
                }

            }
            if(count($parents)>0)
                return api_response($request, $parents, 200, ['category' => $parents]);
            else
                return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function all(Request $request, ApproximatePriceCalculator $approximatePriceCalculator)
    {
        try{
            if ($request->has('location')) {
                $location = $request->location != '' ? $request->location : 4;
            } else {
                if ($request->has('lat') && $request->has('lng')) {
                    $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                    if (!is_null($hyperLocation)) $location = $hyperLocation->location->id; else return api_response($request, null, 404);
                } else $location = 4;
            }

            $subscriptions = ServiceSubscription::all();
            foreach ($subscriptions as $index => $subscription) {
                if (!in_array($location, $subscription->service->locations->pluck('id')->toArray())) {
                    array_forget($subscriptions, $index);
                    continue;
                }
                $service = removeRelationsAndFields($subscription->service);
                $subscription['offers'] = $subscription->getDiscountOffers();
                $price_range = $approximatePriceCalculator->setSubscription($subscription)->getPriceRange();
                $subscription = removeRelationsAndFields($subscription);
                $subscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
                $subscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
                $subscription['price_applicable_for'] = $price_range['price_applicable_for'];
                $subscription['thumb'] = $service['thumb'];
                $subscription['banner'] = $service['banner'];
                $subscription['unit'] = $service['unit'];

            }
            if (count($subscriptions) > 0)
                return api_response($request, $subscriptions, 200, ['subscriptions' => $subscriptions->values()->all()]);
            else
                return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($serviceSubscription,Request $request, ApproximatePriceCalculator $approximatePriceCalculator)
    {
        try {
            if ($request->has('location')) {
                $location = $request->location != '' ? $request->location : 4;
            } else {
                if ($request->has('lat') && $request->has('lng')) {
                    $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                    if (!is_null($hyperLocation)) $location = $hyperLocation->location->id; else return api_response($request, null, 404);
                } else $location = 4;
            }

            $serviceSubscription = ServiceSubscription::find((int)$serviceSubscription);
            if (!in_array($location, $serviceSubscription->service->locations->pluck('id')->toArray()))
                return api_response($request, null, 404);
            $options = $this->serviceQuestionSet($serviceSubscription->service);
            $serviceSubscription['questions'] = json_encode($options, true);
            $answers = collect();
            if ($options)
                foreach ($options as $option) {
                    $answers->push($option["answers"]);
                }

            $price_range = $approximatePriceCalculator->setSubscription($serviceSubscription)->getPriceRange();
            $serviceSubscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
            $serviceSubscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
            $serviceSubscription['price_applicable_for'] = $price_range['price_applicable_for'];
            $serviceSubscription['thumb'] = $serviceSubscription->service['thumb'];
            $serviceSubscription['banner'] = $serviceSubscription->service['banner'];
            $serviceSubscription['unit'] = $serviceSubscription->service['unit'];
            $serviceSubscription['service_min_quantity'] = $serviceSubscription->service['min_quantity'];
            $serviceSubscription['structured_description'] = [
                'All of our partners are background verified.',
                'They will ensure 100% satisfaction'
            ];
            $serviceSubscription['offers'] = $serviceSubscription->getDiscountOffers();
            if ($options) {
                if (count($answers) > 1)
                    $serviceSubscription['service_breakdown'] = $this->breakdown_service_with_min_max_price($answers, $serviceSubscription['min_price'], $serviceSubscription['max_price']);
                else {
                    $total_breakdown = array();
                    foreach ($answers[0] as $index => $answer) {
                        $breakdown = array(
                            'name' => $answer,
                            'indexes' => array($index),
                            'min_price' => $serviceSubscription['min_price'],
                            'max_price' => $serviceSubscription['max_price']
                        );
                        array_push($total_breakdown, $breakdown);
                    }
                    $serviceSubscription['service_breakdown'] = $total_breakdown;
                }

            } else {
                $serviceSubscription['service_breakdown'] = array(array(
                    'name' => $serviceSubscription->service->name,
                    'indexes' => null,
                    'min_price' => $serviceSubscription['min_price'],
                    'max_price' => $serviceSubscription['max_price']
                ));
            }
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

    private function getPriceRangeNew(ServiceSubscription $subscription)
    {
        try {
            $max_price = [];
            $min_price = [];
            $service = $subscription->service;
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
            $max_min_price = array((double)max($max_price) * $service->min_quantity, (double)min($min_price) * $service->min_quantity);
            $offer = $subscription->getDiscountOffer('asc');
            dd($subscription);
            dd($offer);
//            return array((double)max($max_price) * $service->min_quantity, (double)min($min_price) * $service->min_quantity);
        } catch (\Throwable $e) {
            return array(0, 0);
        }
    }


    private function breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }

        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i + 1);

        $result = array();

        foreach ($arrays[$i] as $array_index => $v) {

            foreach ($tmp as $index => $t) {
                $result[] = is_array($t) ?
                    array(
                        'name' => $v . " - " . $t['name'],
                        'indexes' => array_merge(array($array_index), $t['indexes']),
                        'min_price' => $t['min_price'],
                        'max_price' => $t['max_price'],
                    ) :
                    array(
                        'name' => $v . " - " . $t,
                        'indexes' => array($array_index, $index),
                        'min_price' => $min_price,
                        'max_price' => $max_price
                    );
            }
        }

        return $result;
    }
}
