<?php namespace App\Http\Controllers;

use App\Jobs\SendFaqEmail;
use App\Models\AppVersion;
use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\OfferShowcase;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\PosOrder;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Slider;
use App\Models\SliderPortal;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Cache;
use DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\AvailableMethods;
use Sheba\Reports\PdfHandler;
use Sheba\Repositories\PaymentLinkRepository;
use Validator;

class ShebaController extends Controller
{
    use DispatchesJobs;
    private $serviceRepository;
    private $reviewRepository;
    private $paymentLinkrepository;

    public function __construct(ServiceRepository $service_repo, ReviewRepository $review_repo, PaymentLinkRepository $paymentLinkRepository)
    {
        $this->serviceRepository = $service_repo;
        $this->reviewRepository = $review_repo;
        $this->paymentLinkrepository = $paymentLinkRepository;
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

    public function checkTransactionStatus(Request $request, $transactionID, PdfHandler $pdfHandler)
    {
        try {
            $payment = Payment::where('transaction_id', $transactionID)->whereIn('status', ['failed', 'validated', 'completed'])->first();
            if (!$payment) {
                $payment = Payment::where('transaction_id', $transactionID)->first();
                if ($payment->transaction_details && isset(json_decode($payment->transaction_details)->errorMessage)) {
                    $message = 'Your payment has been failed due to ' . json_decode($payment->transaction_details)->errorMessage;
                } else {
                    $message = 'Payment Failed.';
                }
                return api_response($request, null, 404, ['message' => $message]);
            }
            $info = [
                'amount' => $payment->payable->amount,
                'method' => $payment->paymentDetails->last()->readable_method,
                'description' => $payment->payable->description,
                'created_at' => $payment->created_at->format('jS M, Y, h:i A'),
                'invoice_link' => $payment->invoice_link
            ];
            $info = array_merge($info, $this->getInfoForPaymentLink($payment->payable));
            if ($payment->status == 'validated' || $payment->status == 'failed') {
                $message = 'Your payment has been received but there was a system error. It will take some time to update your transaction. Call 16516 for support.';
            } else {
                $message = 'Successful';
            }
            return api_response($request, null, 200, ['info' => $info, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPayments(Request $request)
    {
        try {
            $version_code = (int)$request->header('Version-Code');
            $platform_name = $request->header('Platform-Name');
            $payments = AvailableMethods::get($request->payable_type, $version_code, $platform_name);
            return api_response($request, $payments, 200, [
                'payments' => $payments,
                'discount_message' => 'Pay online and stay relaxed!!!'
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getInfoForPaymentLink(Payable $payable)
    {
        $data = [];
        if ($payable->type == 'payment_link') {
            $payment_link = $this->paymentLinkrepository->getPaymentLinkByLinkId($payable->type_id);
            $user = $payment_link->getPaymentReceiver();
            $data = [
                'payment_receiver' => [
                    'name' => $user->name,
                    'image' => $user->logo,
                    'mobile' => $user->getMobile(),
                    'address' => $user->address
                ],
                'payer' => [
                    'name' => $payable->user->profile->name,
                    'mobile' => $payable->user->profile->mobile
                ]
            ];
        }
        return $data;

    }
}
