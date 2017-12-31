<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaqEmail;
use App\Models\Job;
use App\Models\OfferShowcase;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Slider;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Validator;

class ShebaController extends Controller
{
    use DispatchesJobs;
    private $serviceRepository;
    private $reviewRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function getInfo()
    {
        $job_count = Job::all()->count() + 16000;
        $service_count = Service::where('publication_status', 1)->get()->count();
//        $resource_count = Resource::whereHas('partners', function ($q) {
//            $q->where([
//                ['resource_type', 'Handyman'],
//                ['is_verified', 1]
//            ]);
//        })->get()->count();
        $resource_count = Resource::where('is_verified', 1)->get()->count();
        return response()->json(['service' => $service_count, 'job' => $job_count,
            'resource' => $resource_count,
            'msg' => 'successful', 'code' => 200]);
    }

    public function sendFaq(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'subject' => 'required|string',
                'message' => 'required|string'
            ]);
            if ($validator->fails()) {
                return api_response($request, null, 500, ['message' => $validator->errors()->all()[0]]);
            }
            $this->dispatch(new SendFaqEmail($request->all()));
            return api_response($request, null, 200);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getImages(Request $request)
    {
        $for = null;
        if ($request->has('for')) {
            $for = $request->for == 'app' ? 'is_active_for_app' : 'is_active_for_web';
        }
        $images = Slider::select('id', 'image_link', 'small_image_link', 'target_link', 'target_type', 'target_id', 'is_active_for_web', 'is_active_for_app')->show();
        if (!empty($for)) {
            $images = $images->where($for, 1);
        }
        return count($images) > 0 ? api_response($request, $images, 200, ['images' => $images]) : api_response($request, null, 404);
    }

    public function getOffers()
    {
        $offers = OfferShowcase::select('id', 'thumb', 'title', 'short_description', 'target_link')
            ->where('is_active', 1)->get();
        return response()->json(['offers' => $offers, 'code' => 200]);
    }

    public function getOffer($offer)
    {
        $offer = OfferShowcase::select('id', 'thumb', 'title', 'banner', 'short_description', 'detail_description', 'target_link')
            ->where([
                ['id', $offer],
                ['is_active', 1]
            ])->first();
        return count($offer) > 0 ? response()->json(['offer' => $offer, 'code' => 200]) : response()->json(['code' => 404]);
    }

    public function getSimilarOffer($offer)
    {
        $offer = OfferShowcase::select('id', 'thumb', 'title', 'banner', 'short_description', 'detail_description', 'target_link')
            ->where([
                ['id', '<>', $offer],
                ['is_active', 1]
            ])->get();
        return count($offer) >= 3 ? response()->json(['offer' => $offer, 'code' => 200]) : response()->json(['code' => 404]);
    }

    public function getLeadRewardAmount()
    {
        return response()->json(['code' => 200, 'amount' => constants('AFFILIATION_REWARD_MONEY')]);
    }

    public function getVersions(Request $request)
    {
        try {
            $apps = json_decode(Redis::get('app_versions'));
            if ($apps == null) {
                $version_string = 'itemprop="softwareVersion">';
                $playstore_link = "https://play.google.com/store/apps/details?id=xyz.sheba.";
                $apps = [
                    "customer_app" => $playstore_link . env('CUSTOMER_APP_PACKAGE_NAME'),
                    "bondhu_app" => $playstore_link . env('BONDHU_APP_PACKAGE_NAME'),
                    "resource_app" => $playstore_link . env('RESOURCE_APP_PACKAGE_NAME'),
                    "manager_app" => $playstore_link . env('MANAGER_APP_PACKAGE_NAME')
                ];
                foreach ($apps as $key => $value) {
                    $headers = get_headers($value);
                    if (substr($headers[0], 9, 3) == "200") {
                        $dom = file_get_contents($value);
                        $version = strpos($dom, $version_string);
                        $result_string = trim(substr($dom, $version + strlen($version_string), 15));
                        $final_string = explode(' ', $result_string);
                        $apps[$key] = (int)str_replace('.', '', $final_string[0]);
                    } else {
                        $apps[$key] = 0;
                    }
                }
                Redis::set('app_versions', json_encode($apps));
            }
            return api_response($request, $apps, 200, ['apps' => $apps]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
