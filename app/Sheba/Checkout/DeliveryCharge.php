<?php namespace Sheba\Checkout;


use App\Models\Category;
use App\Models\Partner;
use GuzzleHttp\Client;

class DeliveryCharge
{
    /** @var Partner */
    private $partner;
    /** @var Category */
    private $category;
    private $shebaLogisticDeliveryCharge;
    private $categoryPartnerPivot;


    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }


    public function setCategory(Category $category)
    {
        $this->category = $category;
        $this->shebaLogisticDeliveryCharge = $this->setShebaLogisticsPrice();
        return $this;
    }

    public function setCategoryPartnerPivot($category_partner_pivot)
    {
        $this->categoryPartnerPivot = $category_partner_pivot;
        return $this;
    }

    public function setShebaLogisticsPrice()
    {
        $client = new Client();
        $response = $client->request('GET', config('sheba.logistic_url') . '/parcels/' . $this->category->logistic_parcel_type, [
            'headers' => [
                'app-key' => 'shebalogistic',
                'app-secret' => 'shebalogistic'
            ]
        ]);
        $result = json_decode($response->getBody());
        return isset($result->parcel->price) ? $result->parcel->price : 0;
    }

    public function getDeliveryCharge()
    {
        if ((int)$this->categoryPartnerPivot->uses_sheba_logistic) {
            return $this->category->needsTwoWayLogistic() ? $this->shebaLogisticDeliveryCharge * 2 : $this->shebaLogisticDeliveryCharge;
        } else {
            return (double)$this->categoryPartnerPivot->delivery_charge;
        }
    }


}