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

    public function __construct()
    {
        $this->shebaLogisticDeliveryCharge = $this->setShebaLogisticsPrice();
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }


    public function setCategory(Category $category)
    {
        $this->category = $category;
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
        $response = $client->request('GET', config('sheba.logistic_url') . '/orders/price');
        $result = json_decode($response->getBody());
        return $result->price;
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