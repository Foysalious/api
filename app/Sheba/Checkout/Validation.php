<?php

namespace App\Sheba\Checkout;


use App\Models\Location;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Validation
{
    public $message = '';

    public function isValid(Request $request)
    {
        $selected_services = $this->getSelectedServices(json_decode($request->services));
        $location = Location::find((int)$request->location);
        if (count($selected_services->pluck('category_id')->unique()->toArray()) > 1) {
            $this->message = "Only one category should be selected";
            return 0;
        } elseif ($location) {
            if (!$location->publication_status) {
                $this->message = "Selected location is not published";
                return 0;
            }
        } elseif (Carbon::parse($request->date) < Carbon::today()) {
            $this->message = "Selected Date is not valid";
            return 0;
        } elseif (!$this->isValidTime($request->time)) {
            $this->message = "Selected Time is not valid";
            return 0;
        }
        return 1;
    }

    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::select('id', 'name', 'category_id', 'min_quantity', 'variable_type', 'variables')->where('id', $service->id)->publishedForAll()->first();
            $selected_service['option'] = $service->option;
            $selected_service['quantity'] = $this->getSelectedServiceQuantity($service, (double)$selected_service->min_quantity);
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

    private function getSelectedServiceQuantity($service, $min_quantity)
    {
        if (isset($service->quantity)) {
            $quantity = (double)$service->quantity;
            return $quantity >= $min_quantity ? $quantity : $min_quantity;
        } else {
            return $min_quantity;
        }
    }

    private function isValidTime($time)
    {
        return Carbon::parse(explode('-', $time)[0])->gte(Carbon::now()) ? 1 : 0;
    }
}