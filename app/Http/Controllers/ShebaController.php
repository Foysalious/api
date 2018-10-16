<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaqEmail;
use App\Models\AppVersion;
use App\Models\Category;
use App\Models\Job;
use App\Models\OfferShowcase;
use App\Models\Resource;
use App\Models\ScheduleSlot;
use App\Models\Service;
use App\Models\Slider;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Validator;
use DB;
use Cache;
use Sheba\Payment\Adapters\PayCharged\OrderAdapter;
use Sheba\Payment\Adapters\PayCharged\RechargeAdapter;
use Sheba\Payment\Payment;

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
        try {
            $images = Slider::select('id', 'image_link', 'small_image_link', 'target_link', 'target_type', 'target_id')->show();
            return count($images) > 0 ? api_response($request, $images, 200, ['images' => $images]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
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

    public function getTimeSlots(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app',
            ]);
            $slots = ScheduleSlot::where([
                ['start', '>=', DB::raw("CAST('09:00:00' As time)")],
                ['end', '<=', DB::raw("CAST('21:00:00' As time)")],
            ])->get();
            if ($request->has('for')) {
                $sheba_slots = $this->getShebaSlots($slots);
                return api_response($request, $sheba_slots, 200, ['times' => $sheba_slots]);
            }
            $time_slots = $valid_time_slots = [];
            $current_time = Carbon::now();
            foreach ($slots as $slot) {
                $slot_start_time = Carbon::parse($slot->start);
                $slot_end_time = Carbon::parse($slot->end);
                $time_slot_key = $slot->start . '-' . $slot->end;
                $time_slot_value = $slot_start_time->format('g:i A') . '-' . $slot_end_time->format('g:i A');
                if ($slot_start_time > $current_time) {
                    $valid_time_slots[$time_slot_key] = $time_slot_value;
                }
                $time_slots[$time_slot_key] = $time_slot_value;
            }
            $result = ['times' => $time_slots, 'valid_times' => $valid_time_slots];
            return api_response($request, $result, 200, $result);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getShebaSlots($slots)
    {
        $sheba_slots = [];
        $current_time = Carbon::now();
        foreach ($slots as $slot) {
            $slot_start_time = Carbon::parse($slot->start);
            $slot_end_time = Carbon::parse($slot->end);
            $isValid = 0;
            if ($slot_start_time > $current_time) {
                $isValid = 1;
            }
            array_push($sheba_slots, array(
                'key' => $slot->start . '-' . $slot->end,
                'value' => $slot_start_time->format('g:i A') . '-' . $slot_end_time->format('g:i A'),
                'isValid' => $isValid
            ));
        }
        return $sheba_slots;
    }

    public function getVersions(Request $request)
    {
        try {
            if ($request->has('version') && $request->has('app')) {
                $version = (int)$request->version;
                $app = $request->app;
                $versions = AppVersion::where('tag', $app)->where('version_code', '>', $version)->get();
                $data = array(
                    'title' => !$versions->isEmpty() ? $versions->last()->title : null,
                    'body' => !$versions->isEmpty() ? $versions->last()->body : null,
                    'image_link' => !$versions->isEmpty() ? $versions->last()->image_link : null,
                    'height' => !$versions->isEmpty() ? $versions->last()->height : null,
                    'width' => !$versions->isEmpty() ? $versions->last()->width : null,
                    'has_update' => count($versions) > 0 ? 1 : 0,
                    'is_critical' => count($versions->where('is_critical', 1)) > 0 ? 1 : 0
                );
                return api_response($request, $data, 200, ['data' => $data]);
            }
            $apps = json_decode(Redis::get('app_versions'));
            if ($apps == null) {
                $apps = $this->scrapeAppVersionsAndStoreInRedis();
            }
            return api_response($request, $apps, 200, ['apps' => $apps]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function scrapeAppVersionsAndStoreInRedis()
    {
        $version_string = 'itemprop="softwareVersion">';
        $apps = constants('APPS');
        $final = [];
        foreach ($apps as $key => $value) {
            $headers = get_headers($value);
            $version_code = 0;
            if (substr($headers[0], 9, 3) == "200") {
                $dom = file_get_contents($value);
                $version = strpos($dom, $version_string);
                $result_string = trim(substr($dom, $version + strlen($version_string), 15));
                $final_string = explode(' ', $result_string);
                $version_code = (int)str_replace('.', '', $final_string[0]);
            }
            array_push($final, ['name' => $key, 'version_code' => $version_code, 'is_critical' => 0]);
        }
        Redis::set('app_versions', json_encode($final));
        return $final;
    }

    public function sendCarRentalInfo(Request $request)
    {
        try {
            $ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
            $categories = Category::whereIn('id', $ids)->select('id', 'name', 'parent_id')->get();
            return api_response($request, $categories, 200, ['info' => $categories]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function sendButcherInfo(Request $request)
    {
        try {
            $butcher_service = Service::find((int)env('BUTCHER_SERVICE_ID'));
            if ($butcher_service) {
                $butcher_info = [
                    'id' => $butcher_service->id,
                    'category_id' => $butcher_service->category_id,
                    'name' => $butcher_service->name,
                    'unit' => $butcher_service->unit,
                    'min_quantity' => (double)$butcher_service->min_quantity,
                    'price_info' => json_decode($butcher_service->variables),
                    'date' => "2018-08-21"
                ];
                return api_response($request, $butcher_info, 200, ['info' => $butcher_info]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function checkTransactionStatus(Request $request, $transactionID)
    {
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'user_type' => 'required|in:customer',
                'remember_token' => 'required',
                'paycharge_type' => 'required|in:order,recharge',
                'payment_method' => 'required|in:online,bkash',
                'job_id' => 'sometimes|required',
            ]);
            $class_name = "App\\Models\\" . ucwords($request->user_type);
            $user = $class_name::where([['id', (int)$request->user_id], ['remember_token', $request->remember_token]])->first();
            if (!$user) return api_response($request, null, 404, ['message' => 'User Not found.']);
            if ($request->paycharge_type == 'recharge') $pay_charged = (new RechargeAdapter($user, $transactionID))->getPayCharged();
            else {
                $job = Job::find((int)$request->job_id);
                $pay_charged = (new OrderAdapter($job->partnerOrder, $transactionID))->getPayCharged();
            }
            $paycharge = new Payment($request->payment_method);
            if ($response = $paycharge->isComplete($pay_charged)) {
                return api_response($request, 1, 200, ['info' => array('amount' => $response->amount)]);
            } elseif ($pay_charged = $paycharge->isCompleteByMethods($pay_charged)) {
                return api_response($request, 1, 200, ['info' => array('amount' => $pay_charged->amount),
                    'message' => 'Your payment has been received but there was a system error. It will take some time to update your order. Call 16516 for support.']);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
