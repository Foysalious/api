<?php namespace App\Http\Controllers\Order;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Sheba\OrderPlace\OrderPlace;

class OrderController extends Controller
{
    use ModificationFields;

    public function store(Request $request, OrderPlace $order_place)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $this->validate($request, [
                'name' => 'required|string',
                'services' => 'required|string',
                'sales_channel' => 'required|string',
                'remember_token' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'email' => 'sometimes|email',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet',
                'address_id' => 'required',
                'partner' => 'sometimes|required',
                'partner_id' => 'sometimes|required|numeric',
                'affiliate_id' => 'sometimes|required|numeric',
                'info_call_id' => 'sometimes|required|numeric',
                'affiliation_id' => 'sometimes|required|numeric',
                'vendor_id' => 'sometimes|required|numeric',
                'crm_id' => 'sometimes|required|numeric',
                'business_id' => 'sometimes|required|numeric',
                'voucher' => 'sometimes|required|numeric',
                'emi_month' => 'numeric'
            ], ['mobile' => 'Invalid mobile number!']);
            $this->setModifier($request->customer);
            $order = $order_place->setCustomer($request->customer)
                ->setDeliveryName($request->name)
                ->setDeliveryAddressId($request->address_id)
                ->setPaymentMethod($request->payment_method)
                ->setDeliveryMobile($request->mobile)
                ->setCustomer($request->customer)
                ->setSalesChannel($request->sales_channel)
                ->setPartnerId($request->partner_id)
                ->setSelectedPartnerId($request->partner)
                ->setAdditionalInformation($request->additional_information)->setAffiliationId($request->affiliation_id)
                ->setInfoCallId($request->info_call_id)->setBusinessId($request->business_id)->setCrmId($request->crm_id)
                ->setVoucherId($request->voucher)->setServices($request->services)->setScheduleDate($request->date)
                ->setScheduleTime($request->time)->setVendorId($request->vendor_id)->create();
            if (!$order) return api_response($request, null, 500);
            $job = $order->jobs->first();
            return api_response($request, null, 200, ['job_id' => $job->id, 'order_code' => $order->code(), 'order' => [
                'id' => $order->id,
                'code' => $order->code(),
                'job' => [
                    'id' => $job->id
                ]
            ]]);
        } catch (\Throwable $e) {
            dd($e);
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }
    }
}