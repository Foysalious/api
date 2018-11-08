<?php

namespace App\Http\Controllers;

use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            $partner_list->setAvailability($request->skip_availability)->find($partner);
            if ($request->has('isAvailable')) {
                $partners = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
            }
            if ($partner_list->hasPartners) {
                $start = microtime(true);
                $partner_list->addPricing();
                $time_elapsed_secs = microtime(true) - $start;
                //dump("partner pricing: " . $time_elapsed_secs * 1000);

                $start = microtime(true);
                $partner_list->addInfo();
                $time_elapsed_secs = microtime(true) - $start;
                //dump("total_jobs,total_jobs_of_cat,ongoing_jobs,contact_no,subscription info: " . $time_elapsed_secs * 1000);

                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $start = microtime(true);
                    $partner_list->sortByShebaSelectedCriteria();
                    $time_elapsed_secs = microtime(true) - $start;
                    //dump("sort by sheba criteria: " . $time_elapsed_secs * 1000);
                }
                $partners = $partner_list->partners;
                $partners->each(function ($partner, $key) {
                    array_forget($partner, 'wallet');
                    array_forget($partner, 'package_id');
                    removeRelationsAndFields($partner);
                });
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        }catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}