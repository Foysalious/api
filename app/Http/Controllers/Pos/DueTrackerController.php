<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PartnerPosCustomer;
use Illuminate\Http\Request;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;

class DueTrackerController extends Controller
{
    public function dueList(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $data = $dueTrackerRepository->setPartner($request->partner)->getDueList($request);
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function dueListByProfile(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {
            $request->merge(['customer_id' => $customer_id]);
            $data = $dueTrackerRepository->setPartner($request->partner)->getDueListByProfile($request->partner, $request);
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
    public function setDueDateReminder(Request $request,PartnerPosCustomerRepository $partner_pos_customer_repo){
        try{
            $this->validate($request, ['due_date_reminder' => 'required|date']);
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id,$request->customer_id)->first();
            $partner_pos_customer_repo->update($partner_pos_customer, ['due_date_reminder' => $request->due_date_reminder]);
            return api_response($request, null , 200);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
