<?php

namespace App\Sheba\Checkout;

use App\Models\Location;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Validation
{
    public $message = '';
    public $rentCarId = [];
    public $request = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->rentCarId = collect(explode(',', env('RENT_CAR_IDS')))->map(function ($id) {
            return (int)$id;
        })->toArray();
    }

    public function isValid()
    {
        $selected_services = $this->getSelectedServices(json_decode($this->request->services));
        $category_id = $selected_services->pluck('category_id')->unique()->toArray();
        $location = Location::find((int)$this->request->location);
        if (count($category_id) > 1) {
            $this->message = "Only one category should be selected";
            return 0;
        } elseif (in_array($category_id[0], $this->rentCarId)) {
            if (count($selected_services) > 1) {
                $this->message = "Multiple services selected for rent a car";
                return 0;
            }
        } elseif ($location) {
            if (!$location->publication_status) {
                $this->message = "Selected location is not published";
                return 0;
            }
        } elseif (Carbon::parse($this->request->date) < Carbon::today()) {
            $this->message = "Selected Date is not valid";
            return 0;
        } elseif (!$this->isValidTime($this->request->time)) {
            $this->message = "Selected Time is not valid";
            return 0;
        }
        return 1;
    }

    private function isValidTime($time)
    {
        return Carbon::parse(explode('-', $time)[0])->gte(Carbon::now()) ? 1 : 0;
    }
    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::select('id', 'name', 'unit', 'category_id', 'min_quantity', 'variable_type', 'variables')->where('id', $service->id)->publishedForAll()->first();
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

}