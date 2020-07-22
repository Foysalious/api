<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PartnerPosCustomer;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\DueTracker\Exceptions\UnauthorizedRequestFromExpenseTrackerException;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Reports\PdfHandler;
use Sheba\Usage\Usage;

class DueTrackerController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     */
    public function dueList(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $data = $dueTrackerRepository->setPartner($request->partner)->getDueList($request);
            if (($request->has('download_pdf')) && ($request->download_pdf == 1)){
                $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
                $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
                $pdf_link = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list'.$request->partner->id)->save();
                return api_response($request, null, 200, ['message' => 'PDF download successful','pdf_link' => $pdf_link]);
            }

            if (($request->has('share_pdf')) && ($request->share_pdf == 1)){
                $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
                $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
                $data['pdf_link'] = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list'.$request->partner->id)->save();
            }
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     */
    public function dueListByProfile(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {
            $request->merge(['customer_id' => $customer_id]);
            $data = $dueTrackerRepository->setPartner($request->partner)->getDueListByProfile($request->partner, $request);
            if (($request->has('download_pdf')) && ($request->download_pdf == 1)) {
                $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
                $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
                $pdf_link = (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer_'.$customer_id)->save();
                return api_response($request, null, 200, ['message' => 'PDF download successful','link'  => $pdf_link]);
            }
            if (($request->has('share_pdf')) && ($request->share_pdf == 1)){
                $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
                $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
                $data['pdf_link'] = (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer_'.$customer_id)->save();
            }
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     */
    public function store(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
                'type' => 'required|in:due,deposit'
            ]);
            $request->merge(['customer_id' => $customer_id]);
            $response = $dueTrackerRepository->setPartner($request->partner)->store($request->partner, $request);

            (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->manager_resource);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     */
    public function update(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {

            $this->validate($request, [
                'entry_id' => 'required',
                'attachment_should_remove' => 'sometimes|array'
            ]);

            $request->merge(['customer_id' => $customer_id]);
            $response = $dueTrackerRepository->setPartner($request->partner)->update($request->partner, $request);
            return api_response($request, $response, 200, ['data' => $response]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param PartnerPosCustomerRepository $partner_pos_customer_repo
     * @return JsonResponse
     */
    public function setDueDateReminder(Request $request, PartnerPosCustomerRepository $partner_pos_customer_repo)
    {
        try {
            $this->validate($request, ['due_date_reminder' => 'required|date']);
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id, $request->customer_id)->first();
            if (empty($partner_pos_customer))
                throw new InvalidPartnerPosCustomer();
            $this->setModifier($request->partner);
            $partner_pos_customer_repo->update($partner_pos_customer, ['due_date_reminder' => $request->due_date_reminder]);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     */
    public function dueDateWiseCustomerList(Request $request, DueTrackerRepository $dueTrackerRepository)
    {

        try {
            $request->merge(['balance_type' => 'due']);
            $dueList = $dueTrackerRepository->setPartner($request->partner)->getDueList($request, false);
            $response = $dueTrackerRepository->generateDueReminders($dueList, $request->partner);
            return api_response($request, null, 200, ['data' => $response]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     */
    public function getDueCalender(Request $request, DueTrackerRepository $dueTrackerRepository)
    {

        try {
            $this->validate($request, ['month' => 'required', 'year' => 'required']);
            $request->merge(['balance_type' => 'due']);
            $dueList = $dueTrackerRepository->setPartner($request->partner)->getDueList($request, false);
            $response = $dueTrackerRepository->generateDueCalender($dueList, $request);
            return api_response($request, null, 200, ['data' => $response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $entry_id
     * @return JsonResponse
     */
    public function delete(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $entry_id)
    {
        try {
            $dueTrackerRepository->setPartner($request->partner)->removeEntry($entry_id);
            return api_response($request, true, 200);
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     */
    public function sendSMS(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        try {
            $request->merge(['customer_id' => $customer_id]);
            $this->validate($request, ['type' => 'required|in:due,deposit', 'amount' => 'required', 'payment_link' => 'required_if:type,due']);
            $dueTrackerRepository->sendSMS($request);
            return api_response($request, true, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidPartnerPosCustomer $e) {
            $message = "Invalid pos customer for this partner";
            return api_response($request, $message, 403, ['message' => $message]);
        } catch(InsufficientBalance $e) {
            $message = "Insufficient Balance";
            return api_response($request, $message, 401, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     */
    public function getFaqs(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $faqs = $dueTrackerRepository->getFaqs();
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function createPosOrderPayment(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
                'pos_order_id' => 'required',
                'payment_method'    => 'required|string|in:' . implode(',', config('pos.payment_method')),
                'api_key' => 'required'
            ]);
            if($request->api_key != config('expense_tracker.api_key'))
                throw new UnauthorizedRequestFromExpenseTrackerException();
            $dueTrackerRepository->createPosOrderPayment($request->amount, $request->pos_order_id,$request->payment_method);
            return api_response($request, true, 200, ['message' => 'Pos Order Payment created successfully']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (UnauthorizedRequestFromExpenseTrackerException $e) {
            $message = "Unauthorized Request";
            return api_response($request, $message, 401, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
