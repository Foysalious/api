<?php namespace App\Http\Controllers;

use App\Jobs\SendFaqEmail;
use App\Models\AppVersion;
use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\OfferShowcase;
use App\Models\Payment;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Slider;
use App\Models\SliderPortal;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Validator;
use DB;
use Cache;

class ShebaController extends Controller
{
    use DispatchesJobs;
    private $serviceRepository;
    private $reviewRepository;

    public function __construct(ServiceRepository $service_repo, ReviewRepository $review_repo)
    {
        $this->serviceRepository = $service_repo;
        $this->reviewRepository = $review_repo;
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
            if ($request->has('is_business') && (int)$request->is_business) {
                $portal_name = 'manager-app';
                $screen = 'eshop';

                if (!$request->has('location')) $location = 4;
                else $location = $request->location;
            } else {
                if ($request->has('location')) {
                    $location = $request->location;
                } else {
                    if ($request->has('lat') && $request->has('lng')) {
                        $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                        if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
                    }
                }

                $portal_name = $request->portal;
                $screen = $request->screen;
            }

            $slider = $this->getSliderWithSlides($location, $portal_name, $screen);

            if (!is_null($slider)) return api_response($request, $slider->slides, 200, ['images' => $slider->slides]);
            else return api_response($request, null, 404);

            /**
             * Previous Codes, Left Written Until QA
             *
             * $images = Slider::select('id', 'image_link', 'small_image_link', 'target_link', 'target_type', 'target_id');
             * if ($request->has('is_business') && (int)$request->is_business) {
             * $images = $images->showBusiness()->map(function ($image) {
             * $image['target_type'] = $image['target_type'] ? explode('\\', $image['target_type'])[2] : null;
             * return $image;
             * });
             * } else {
             * $images = $images->show();
             * }
             * return count($images) > 0 ? api_response($request, $images, 200, ['images' => $images]) : api_response($request, null, 404);*/
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $location
     * @param $portal_name
     * @param $screen
     * @return Slider
     */
    private function getSliderWithSlides($location, $portal_name, $screen)
    {
        $sliderPortal = SliderPortal::with('slider')->whereHas('slider', function ($query) use ($location) {
            $query->where('is_published', 1);
        })->where('portal_name', $portal_name)->where('screen', $screen)->first();

        $slider = $sliderPortal->slider()->with(['slides' => function ($q) use ($location) {
            $q->where('location_id', $location)->orderBy('order');
        }])->first();

        return $slider;
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
                'user_id' => 'numeric',
                'user_type' => 'in:customer',
                'remember_token' => 'string',
                'paycharge_type' => 'in:order,recharge',
                'payment_method' => 'in:online,bkash',
                'job_id' => 'sometimes|required',
            ]);
            $payment = Payment::where('transaction_id', $transactionID)->whereIn('status', ['failed', 'validated', 'completed'])->first();
            if (!$payment) return api_response($request, null, 404, ['message' => 'Payment Not found.']);
            $info = array('amount' => $payment->payable->amount);
            if ($payment->status == 'validated' || $payment->status == 'failed') {
                return api_response($request, 1, 200, ['info' => $info,
                    'message' => 'Your payment has been received but there was a system error. It will take some time to update your transaction. Call 16516 for support.']);
            } else {
                return api_response($request, 1, 200, ['info' => $info]);
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

    public function getPayments(Request $request)
    {
        try {
            $version_code = (int)$request->header('Version-Code');
            $payments = array(
                array(
                    'name' => 'Sheba Credit',
                    'is_published' => 1,
                    'description' => '',
                    'asset' => 'sheba_credit',
                    'method_name' => 'wallet'
                ),
                array(
                    'name' => 'bKash Payment',
                    'is_published' => 1,
                    'description' => '',
                    'asset' => 'bkash',
                    'method_name' => 'bkash'
                ),
                array(
                    'name' => 'City Bank',
                    'is_published' => $version_code ? ($version_code > 30112 ? 1 : 0) : 1,
                    'description' => '',
                    'asset' => 'cbl',
                    'method_name' => 'cbl'
                ),
                array(
                    'name' => 'Other Debit/Credit',
                    'is_published' => 1,
                    'description' => '',
                    'asset' => 'ssl',
                    'method_name' => 'online'
                )
            );
            return api_response($request, $payments, 200, ['payments' => $payments]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
