<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\Reports\PdfHandler;

class DueTrackerController extends Controller
{
    public function dueList(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $data = $dueTrackerRepository->setPartner($request->partner)->getDueList($request);
            if ($request->has('download_pdf')) return (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list')->download();
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            dd($e);
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

    public function store(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {
            $request->merge(['customer_id' => $customer_id]);
            $response = $dueTrackerRepository->setPartner($request->partner)->store($request->partner, $request);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function setDueDateReminder(Request $request, PartnerPosCustomerRepository $partner_pos_customer_repo)
    {
        try {
            $this->validate($request, ['due_date_reminder' => 'required|date']);
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id, $request->customer_id)->first();
            $partner_pos_customer_repo->update($partner_pos_customer, ['due_date_reminder' => $request->due_date_reminder]);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
