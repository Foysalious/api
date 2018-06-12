<?php

namespace App\Http\Controllers;

use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PartnerLocationController extends Controller
{
    public function getPartners(Request $request)
    {
        try {
            $this->validate($request, [
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'services' => 'required|string',
                'isAvailable' => 'sometimes|required',
                'partner' => 'sometimes|required',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            $partner = $request->has('partner') ? $request->partner : null;
            $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, array('lat' => (double)$request->lat, 'lng' => (double)$request->lng));
            $partner_list->find($partner);
            if ($request->has('isAvailable')) {
                $partners = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
            }
            if ($partner_list->hasPartners) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                $partner_list->calculateAverageRating();
                $partner_list->calculateTotalRatings();
                $partner_list->calculateOngoingJobs();
                $partner_list->sortByShebaSelectedCriteria();
                $partners = $partner_list->partners;
                $partners->each(function ($partner, $key) {
                    array_forget($partner, 'wallet');
                    removeRelationsAndFields($partner);
                });
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}