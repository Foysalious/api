<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    /** @var Customer */
    private $customer;
    /** @var bool */
    private $isAdvanced;
    /** @var bool */
    private $isUnique;

    protected $fields = [
        "id" => "ID",
        "name" => "Name",
        "mobile" => "Mobile",
        "email" => "Email",
        "mobile_verified" => "Mobile Verified",
        "email_verified" => "Email Verified",
        "address" => "Address",
        "gender" => "Gender",
        "dob" => "DOB",
        "age" => "Age",
        "is_returning" => "Is Returning",
        "no_of_orders" => "No of Orders",
        "closed_orders" => "Closed Orders",
        "cancelled_orders" => "Cancelled Orders",
        "last_order_date" => "Last Order Date",
        "primary_location" => "Primary Location",
        "all_locations" => "All Locations",
        "primary_channel" => "Primary Channel",
        "all_channels" => "All Channels",
        "purchase_amount" => "Purchase Amount",
        "most_purchased_service" => "Most Purchased Service",
        "service_tried" => "Service Tried",
        "referral_code" => "Referral Code",
        "all_purchased_service" => "All Purchased Service",
        "created_by" => "Created By",
        "created_at" => "Created At"
    ];

    /**
     * @param $is_advanced bool
     * @return $this
     */
    public function setIsAdvanced($is_advanced)
    {
        $this->isAdvanced = $is_advanced;
        return $this;
    }

    /**
     * @param $is_unique bool
     * @return $this
     */
    public function setIsUnique($is_unique)
    {
        $this->isUnique = $is_unique;
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $data = [
            "id" => $this->customer->id,
            "name" => $this->customer->profile->name,
            "mobile" => $this->customer->profile->mobile,
            "email" => $this->customer->profile->email,
            "mobile_verified" => $this->customer->profile->mobile_verified,
            "email_verified" => $this->customer->profile->email_verified,
            "address" => $this->customer->profile->address,
            "gender" => $this->customer->profile->gender,
            "dob" => $this->customer->profile->dob,
            "age" => !$this->customer->profile->dob ? null : $this->customer->profile->dob->diff(Carbon::now()),
            "created_by" => $this->customer->created_by_name,
            "created_at" => $this->customer->created_at
        ];

        $all_locations = $this->customer->orderLocationWithCounts()->pluck('usage', 'location.name');
        $primary_location = $all_locations->first()['location'];
        $all_channels = $this->customer->orderChannelWithCounts();
        $all_purchased_service = $this->customer->purchasedServiceWithCount();

        if($this->isAdvanced) {
            if($this->isUnique) $data["is_returning"] = $this->customer->is_returning;

            $data += [
                "no_of_orders" => $this->customer->orders->count(),
                "closed_orders" => $this->customer->ordersOnStatus('Closed')->count(),
                "cancelled_orders" => $this->customer->ordersOnStatus('Cancelled')->count(),
                "last_order_date" => $this->customer->lastOrder() ? $this->customer->lastOrder()->created_at : null,
                "primary_location" => $primary_location ? $primary_location->name : null,
                "all_locations" => $all_locations,
                "primary_channel" => $all_channels->keys()->first() ?: null,
                "all_channels" => $all_channels,
                "purchase_amount" => $this->customer->totalPurchaseAmount(),
                "most_purchased_service" => $all_purchased_service->keys()->first() ?: null,
                "service_tried" => $all_purchased_service->keys()->count(),
                "referral_code" => $this->customer->vouchers->isEmpty() ? null : $this->customer->vouchers->first()->code,
                "all_purchased_service" => $all_purchased_service
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getForView()
    {
        $view_data = $this->convertToViewKeys();
        $view_data['Name'] = $view_data['Name'] ?: "N/S";
        $view_data['Mobile'] = $view_data['Mobile'] ?: "N/S";
        $view_data['Email'] = $view_data['Email'] ?: "N/S";
        $view_data['Mobile Verified'] = $view_data['Mobile Verified'] ? 'Verified' : 'Unverified';
        $view_data['Email Verified'] = $view_data['Email Verified'] ? 'Verified' : 'Unverified';
        $view_data['DOB'] = $view_data['DOB'] ? $view_data['DOB']->format('d M Y') : "N/D";
        $view_data['Age'] = $view_data['Age'] ? $view_data['Age']->format('%y years, %m months and %d days') : "N/D";
        $view_data['Created At'] = $view_data['Created At']->format('d M Y H:i');

        if($this->isAdvanced) {
            if($this->isUnique) $view_data['Is Returning'] = $view_data['Is Returning'] ? "Yes" : "No";

            $view_data['Last Order Date'] = $view_data['Last Order Date'] ? $view_data['Last Order Date']->format('d M Y H:i') : "N/A";
            $view_data['Primary Location'] = $view_data['Primary Location'] ?: "None";
            $view_data['All Locations'] = $view_data['All Locations'] ? $this->makeViewString($view_data['All Locations']) : "None";
            $view_data['Primary Channel'] = $view_data['Primary Channel'] ?: "None";
            $view_data['All Channels'] = $view_data['All Channels'] ? $this->makeViewString($view_data['All Channels']): "None";
            $view_data['Most Purchased Service'] = $view_data['Most Purchased Service'] ?: "None";
            $view_data['Referral Code'] = $view_data['Referral Code'] ?: "None";
            $view_data['All Purchased Service'] = $view_data['All Purchased Service'] ? $this->makeViewString($view_data['All Purchased Service']) : "None";
        }

        return $view_data;
    }

    private function makeViewString(Collection $data)
    {
        return str_replace(str_split("\"{}[]"), "", $data->toJson());
    }

    public function getForTable()
    {
        $data = $this->get();
        unset($data['age'], $data['is_returning']);
        $data["all_locations"] = $data["all_locations"]->toJson();
        $data["all_channels"] = $data["all_channels"]->toJson();
        $data["all_purchased_service"] = $data["all_purchased_service"]->toJson();
        return $data;
    }
}