<?php namespace App\Sheba;

use App\Models\Partner;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;

class PartnerGeneralSettings
{
    protected $client;
    public function __construct(DeliveryServerClient $client)
    {
        $this->client = $client;
    }

    private $partner;
    private $token;

    /**
     * @param mixed $partner
     * @return PartnerGeneralSettings
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $token
     * @return PartnerGeneralSettings
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }


    public function getGeneralSettings()
    {
        $data['delivery_settings'] = $this->getDeliverySettings();
        return $data;
    }

    private function getDeliverySettings()
    {
        $partnerDeliveryInformation = PartnerDeliveryInformation::where('partner_id', $this->partner->id)->first();
        $delivery_method = !empty($partnerDeliveryInformation) && ($partnerDeliveryInformation->delivery_vendor != Methods::OWN_DELIVERY) ?  $this->getPreferredDeliveryMethod() : Methods::OWN_DELIVERY;

        $preferred_delivery_method = config('pos_delivery.vendor_list')[$delivery_method];
        $preferred_delivery_method['key'] = $delivery_method;

        return [
            'use_sdelivery' => $delivery_method != Methods::OWN_DELIVERY,
            'preferred_delivery_method' => $preferred_delivery_method,
            'delivery_price' => $delivery_method == Methods::OWN_DELIVERY ? (double)$this->getDeliveryCharge($this->partner) : null
        ];
    }

    private function getPreferredDeliveryMethod()
    {
        return $this->client->setToken($this->token)->get('merchants/info')['data']['preferred_logistic_partner_name'];
    }

    public function getDeliveryCharge(Partner $partner)
    {
        if(!$partner->isMigrated(Modules::POS))
            return $partner->delivery_charge;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($partner->id)->getPartnerDetails()['partner']['delivery_charge'];
    }




}