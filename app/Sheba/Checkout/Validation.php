<?php

namespace App\Sheba\Checkout;

use App\Models\Location;
use App\Models\Partner;
use App\Models\ScheduleSlot;
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
        $selected_services = json_decode($this->request->services);
        if (empty($selected_services)) {
            $this->message = "Please select a service";
            return 0;
        }
        $selected_services = $this->getSelectedServices($selected_services);
        if ($selected_services->count() == 0) {
            $this->message = "Please select a service";
            return 0;
        }
        $category_id = $selected_services->pluck('category_id')->unique()->toArray();
//        $location = Location::where('id', (int)$this->request->location)->published()->first();
//        if (!$location) {
//            $this->message = "Selected location is not valid";
//            return 0;
//        } else
        if (count($category_id) > 1) {
            $this->message = "You can select only one category";
            return 0;
        } elseif (in_array($category_id[0], $this->rentCarIds)) {
            if (count($selected_services) > 1) {
                $this->message = "You can select only one service for rent a car";
                return 0;
            }
        }

        if (!$this->request->has('skip_availability') || !$this->request->skip_availability) {
            if (!$this->isValidDate($this->request->date) || is_null($this->request->date)) {
                $this->message = "Selected date is not valid";
                return 0;
            } elseif (!$this->isValidTime($this->request->time) || is_null($this->request->time)) {
                $this->message = "Selected time is not valid";
                return 0;
            }
        }

        if ($this->request->has('partner_id')) {
            $partner = Partner::find($this->request->partner_id);
            if (!$partner) {
                $this->message = "No Partner Found";
                return 0;
            }
            $resource = $this->request->customer->profile->resource;
            if (!$resource) {
                $this->message = "No Resource Found";
                return 0;
            } else {
                if (!$resource->isManager($partner)) {
                    $this->message = "Resource Doesn't belong to this partner";
                    return 0;
                }
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
//        $slots = ScheduleSlot::shebaSlots()->get();
//        $exits = false;
//
//        $start_time = trim(explode('-', $time)[0]);
//        $start_time = explode(':', $start_time);
//        $start_time = trim($start_time[0]) . ':' . trim($start_time[1]) . ':00';
//        $end_time = trim(explode('-', $time)[1]);
//        $end_time = explode(':', $end_time);
//        $end_time = trim($end_time[0]) . ':' . trim($end_time[1]) . ':00';
//
//        foreach ($slots as $slot) {
//            if ($start_time == $slot->start && $end_time == $slot->end) {
//                $exits = true;
//                break;
//            }
//        }
//        return $exits && Carbon::parse($this->request->date . explode('-', $time)[0])->gte(Carbon::now()) ? 1 : 0;
        return Carbon::parse($this->request->date . ' ' . explode('-', $time)[0])->gte(Carbon::now()) ? 1 : 0;
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