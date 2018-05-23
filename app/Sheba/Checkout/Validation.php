<?php

namespace App\Sheba\Checkout;

use App\Models\Location;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Validation
{
    public $message = '';
    public $rentCarIds = [];
    public $request = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->rentCarIds = array_map('intval', explode(',', env('RENT_CAR_IDS')));
    }

    public function isValid()
    {
        $selected_services = $this->getSelectedServices(json_decode($this->request->services));
        $category_id = $selected_services->pluck('category_id')->unique()->toArray();
        $location = Location::where('id', (int)$this->request->location)->published()->first();
        if (!$location) {
            $this->message = "Selected location is not valid";
            return 0;
        } elseif (!$this->isValidDate($this->request->date)) {
            $this->message = "Selected Date is not valid";
            return 0;
        } elseif (!$this->isValidTime($this->request->time)) {
            $this->message = "Selected Time is not valid";
            return 0;
        } elseif (count($selected_services) == 0) {
            $this->message = "Selected service is not valid";
            return 0;
        } elseif (count($category_id) > 1) {
            $this->message = "You can select only one category";
            return 0;
        } elseif (in_array($category_id[0], $this->rentCarIds)) {
            if (count($selected_services) > 1) {
                $this->message = "You can select only one service for rent a car";
                return 0;
            }
        }
        return 1;
    }

    private function isValidDate($date)
    {
        return Carbon::parse($date) >= Carbon::today();
    }

    private function isValidTime($time)
    {
        return Carbon::parse($this->request->date . explode('-', $time)[0])->gte(Carbon::now()) ? 1 : 0;
    }

    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::where('id', $service->id)->publishedForAll()->first();
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

}