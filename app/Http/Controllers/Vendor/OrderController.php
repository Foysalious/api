<?php namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Jobs\Customer\CreateReferral;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Order;
use App\Models\Profile;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use App\Transformers\CustomSerializer;
use App\Transformers\JobTransformer;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Services\FormatServices;
use Sheba\Voucher\Creator\Referral;
use Throwable;

class OrderController extends Controller
{
    use Helpers, DispatchesJobs;

    /**
     * @param $order
     * @param Request $request
     * @return JsonResponse
     */
    public function show($order, Request $request)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $order = Order::find((int)$order);
            if ($request->vendor->id !== $order->vendor_id) return response()->json(['data' => null]);
            $job = $order->partnerOrders[0]->jobs[0]->id;
            $customer = $order->customer;
            $job = $this->api->get('/v2/customers/' . $customer->id . '/jobs/' . $job . '?remember_token=' . $customer->remember_token);
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item(json_decode($job->toJson()), new JobTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    /**
     * @param $order
     * @param Request $request
     * @param FormatServices $formatServices
     * @return JsonResponse
     */
    public function getBills($order, Request $request, FormatServices $formatServices)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $order = Order::find((int)$order);
            if ($request->vendor->id !== $order->vendor_id) return response()->json(['data' => null]);
            $job = $order->partnerOrders[0]->jobs[0];
            $customer = $order->customer;
            $service_list = $formatServices->setJob($job)->formatServices();
            $job = $this->api->get('/v2/customers/' . $customer->id . '/jobs/' . $job->id . '/bills?remember_token=' . $customer->remember_token);
            return response()->json(['data' =>
                [
                    'discounted_price' => $job->get('total'),
                    'total_price_with_logistic' => $job->get('total'),
                    'total_price_without_logistic' => $job->get('total_without_logistic'),
                    'original_price' => $job->get('original_price'),
                    'discount' => $job->get('discount'),
                    'material_price' => $job->get('material_price'),
                    'delivery_charge' => $job->get('delivery_charge'),
                    'paid' => $job->get('paid'),
                    'due' => $job->get('due'),
                    'services' => $job->get('services'),
                    'service_list' => $service_list
                ]]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    public function placeOrder(Request $request)
    {
        try {
            $request->merge(['mobile' => trim(formatMobile($request->mobile))]);
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'services' => 'required|string',
                'partner' => 'required',
                'mobile' => 'required|string|mobile:bd',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'address' => 'required',
                'is_on_premise' => 'sometimes|numeric',
                'name' => 'required'
            ], ['mobile' => 'Invalid mobile number!']);
            $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->first();
            $request->merge(['payment_method' => 'cod', 'vendor_id' => $request->vendor->id, 'sales_channel' => 'Store']);
            $profile = Profile::where('mobile', $request->mobile)->first();
            if ($profile) {
                $customer = $profile->customer;
                if (!$customer) $customer = $this->createCustomer($profile);
            } else {
                $profile = $this->createProfile();
                $customer = $this->createCustomer($profile);
            }
            $order = new Checkout($customer);
            $address = (new AddressValidator())->isAddressNameExists($customer->delivery_addresses, $request->address);
            if (!$address) {
                $address = new CustomerDeliveryAddress();
                $address->address = $request->address;
                $address->name = $request->name;
                $address->geo_informations = json_encode(['lat' => $request->lat, 'lng' => $request->lng]);
                $address->location_id = $hyper_local->location->id;
                $address->customer_id = $customer->id;
                $address->mobile = formatMobile($request->mobile);
                $address->save();
            }
            $request->merge(['address_id' => $address->id]);
            $order = $order->placeOrder($request);
            if ($order) return response()->json(['data' => ['order_id' => $order->id, 'message' => 'SUCCESSFUL']]);
            else return response()->json(['data' => null]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    private function createProfile()
    {
        $profile = new Profile();
        $profile->mobile = \request()->mobile;
        $profile->name = \request()->name;
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    /**
     * @param Profile $profile
     * @return Customer
     */
    private function createCustomer(Profile $profile)
    {
        $customer = new Customer();
        $customer->profile_id = $profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        dispatch(new CreateReferral($customer));
        return $customer;
    }
}