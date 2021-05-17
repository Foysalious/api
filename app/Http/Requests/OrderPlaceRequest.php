<?php namespace App\Http\Requests;


use App\Models\Customer;
use Carbon\Carbon;
use Sheba\OrderPlace\OrderPlace;
use Sheba\UserAgentInformation;

class OrderPlaceRequest extends ApiRequest
{
    /** @var Customer */
    private $customerObject;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'services' => 'required|string',
            'sales_channel' => 'required|string',
            'mobile' => 'required|string|mobile:bd',
            'email' => 'sometimes|email',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet,bondhu_balance',
            'address' => 'required_without:address_id',
            'address_id' => 'required_without:address|numeric',
            'partner' => 'sometimes|required',
            'partner_id' => 'sometimes|required|numeric',
            'affiliate_id' => 'sometimes|required|numeric',
            'info_call_id' => 'sometimes|required|numeric',
            'affiliation_id' => 'sometimes|required|numeric',
            'vendor_id' => 'sometimes|required|numeric',
            'crm_id' => 'sometimes|required|numeric',
            'business_id' => 'sometimes|required|numeric',
            'voucher' => 'sometimes|required|numeric',
            'emi_month' => 'numeric',
            'created_by' => 'numeric',
            'created_by_name' => 'string',
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        $messages['mobile'] = 'Invalid mobile number.';
        return $messages;
    }

    public function getUserAgentInfo()
    {
        /** @var UserAgentInformation $info */
        $info = app(UserAgentInformation::class);
        $info->setRequest($this);
        return $info;
    }

    public function getCustomer()
    {
        if ($this->customerObject) return $this->customerObject;

        $request = $this;
        $this->customerObject = $request->customer instanceof Customer ? $request->customer : Customer::find($request->customer);
        return $this->customerObject;
    }

    /**
     * @return OrderPlace
     * @throws \App\Exceptions\RentACar\DestinationCitySameAsPickupException
     * @throws \App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException
     * @throws \App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException
     */
    public function buildOrderPlace()
    {
        $request = $this;
        /** @var OrderPlace $order_place */
        $order_place = app(OrderPlace::class);
        $order_place
            ->setCustomer($request->getCustomer())
            ->setDeliveryName($request->name)
            ->setDeliveryAddressId($request->address_id)
            ->setDeliveryAddress($request->address)
            ->setPaymentMethod($request->payment_method)
            ->setDeliveryMobile($request->mobile)
            ->setSalesChannel($request->sales_channel)
            ->setPartnerId($request->partner_id)
            ->setSelectedPartnerId($request->partner)
            ->setAdditionalInformation($request->additional_information)
            ->setAffiliationId($request->affiliation_id)
            ->setInfoCallId($request->info_call_id)
            ->setBusinessId($request->business_id)
            ->setCrmId($request->crm_id)
            ->setVoucherId($request->voucher)
            ->setServices($request->services)
            ->setScheduleDate($request->date)
            ->setScheduleTime($request->time)
            ->setVendorId($request->vendor_id)
            ->setUserAgentInformation($request->getUserAgentInfo());

        return $order_place;
    }
}
