<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\DueTracker\Exceptions\UnauthorizedRequestFromExpenseTrackerException;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\ModificationFields;
use Sheba\PaymentLink\Creator as PaymentLinkCreator;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Usage\Usage;

class DueTrackerController extends Controller
{
    use ModificationFields;
    private $entryRepo;
    private $paymentLinkCreator;

    public function __construct(EntryRepository $entry_repo, PaymentLinkCreator $paymentLinkCreator)
    {
        $this->entryRepo = $entry_repo;
        $this->paymentLinkCreator = $paymentLinkCreator;
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param PartnerRepositoryInterface $partner_repo
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     * @throws NotAssociativeArray
     * @throws \Throwable
     */
    public function dueList(Request $request, DueTrackerRepository $dueTrackerRepository,PartnerRepositoryInterface $partner_repo)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 420);

        if (!$request->partner->expense_account_id) {
            $account = $this->entryRepo->createExpenseUser($request->partner);
            $this->setModifier($request->partner);
            $data = ['expense_account_id' => $account['id']];
            $partner_repo->update($request->partner, $data);
        }
        $data = $dueTrackerRepository->setPartner($request->partner)->getDueList($request);
        if (($request->has('download_pdf')) && ($request->download_pdf == 1)){
            $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
            $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
            $pdf_link = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list')->save(true);
            return api_response($request, null, 200, ['message' => 'PDF download successful','pdf_link' => $pdf_link]);
        }

        if (($request->has('share_pdf')) && ($request->share_pdf == 1)){
            $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
            $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
            $data['pdf_link'] = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list')->save(true);
        }
        return api_response($request, $data, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     * @throws InvalidPartnerPosCustomer
     * @throws NotAssociativeArray
     * @throws \Throwable
     */
    public function dueListByProfile(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 420);

        $request->merge(['customer_id' => $customer_id]);
        $data = $dueTrackerRepository->setPartner($request->partner)->getDueListByProfile($request->partner, $request);
        if (($request->has('download_pdf')) && ($request->download_pdf == 1)) {
            $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
            $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
            $pdf_link = (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer')->save(true);
            return api_response($request, null, 200, ['message' => 'PDF download successful','link'  => $pdf_link]);
        }
        if (($request->has('share_pdf')) && ($request->share_pdf == 1)){
            $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
            $data['end_date'] = $request->has("end_date") ? $request->end_date : null;
            $data['pdf_link'] = (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer')->save(true);
        }
        return api_response($request, $data, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     */
    public function store(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        $this->validate($request, [
            'amount' => 'required',
            'type' => 'required|in:due,deposit'
        ]);
        $request->merge(['customer_id' => $customer_id]);
        $response = $dueTrackerRepository->setPartner($request->partner)->store($request->partner, $request);

        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->manager_resource);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     * @throws InvalidPartnerPosCustomer
     */
    public function update(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        $this->validate($request, [
            'entry_id' => 'required',
            'attachment_should_remove' => 'sometimes|array'
        ]);

        $request->merge(['customer_id' => $customer_id]);
        $response = $dueTrackerRepository->setPartner($request->partner)->update($request->partner, $request);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param PartnerPosCustomerRepository $partner_pos_customer_repo
     * @return JsonResponse
     * @throws InvalidPartnerPosCustomer
     */
    public function setDueDateReminder(Request $request, PartnerPosCustomerRepository $partner_pos_customer_repo)
    {
        $this->validate($request, ['due_date_reminder' => 'required|date']);
        $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id, $request->customer_id)->first();
        if (empty($partner_pos_customer)) throw new InvalidPartnerPosCustomer();
        $this->setModifier($request->partner);
        $partner_pos_customer_repo->update($partner_pos_customer, ['due_date_reminder' => $request->due_date_reminder]);
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     */
    public function dueDateWiseCustomerList(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        $request->merge(['balance_type' => 'due']);
        $dueList = $dueTrackerRepository->setPartner($request->partner)->getDueList($request, false);
        $response = $dueTrackerRepository->generateDueReminders($dueList, $request->partner);
        return api_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     */
    public function getDueCalender(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        $this->validate($request, ['month' => 'required', 'year' => 'required']);
        $request->merge(['balance_type' => 'due']);
        $dueList = $dueTrackerRepository->setPartner($request->partner)->getDueList($request, false);
        $response = $dueTrackerRepository->generateDueCalender($dueList, $request);
        return api_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $entry_id
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     */
    public function delete(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $entry_id)
    {
        $dueTrackerRepository->setPartner($request->partner)->removeEntry($entry_id);
        return api_response($request, true, 200);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @param $partner
     * @param $customer_id
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendSMS(Request $request, DueTrackerRepository $dueTrackerRepository, $partner, $customer_id)
    {
        $request->merge(['customer_id' => $customer_id]);
        $this->validate($request, ['type' => 'required|in:due,deposit', 'amount' => 'required']);
        if ($request->type == 'due') {
            $request['payment_link'] = $dueTrackerRepository->createPaymentLink($request, $this->paymentLinkCreator);
        }
        $dueTrackerRepository->sendSMS($request);
        return api_response($request, true, 200);
    }

    /**
     * @param Request $request
     * @param DueTrackerRepository $dueTrackerRepository
     * @return JsonResponse
     */
    public function getFaqs(Request $request, DueTrackerRepository $dueTrackerRepository)
    {
        $faqs = $dueTrackerRepository->getFaqs();
        return api_response($request, $faqs, 200, ['faqs' => $faqs]);
    }

    /**
     * @throws UnauthorizedRequestFromExpenseTrackerException
     */
    public function createPosOrderPayment(Request $request, PosOrderPaymentRepository $posOrderPaymentRepository)
    {
        $this->validate($request, [
            'amount' => 'required',
            'pos_order_id' => 'required',
            'payment_method_en' => 'sometimes|string|in:' . implode(',', config('pos.payment_method')),
            'payment_method_bn' => 'sometimes',
            'payment_method_icon' => 'sometimes',
            'api_key' => 'required',
            'expense_account_id' => 'sometimes'
        ]);
        if($request->api_key != config('expense_tracker.api_key'))
            throw new UnauthorizedRequestFromExpenseTrackerException("Unauthorized Request");
        $method_details = ['payment_method_bn' => $request->payment_method_bn, 'payment_method_icon' => $request->payment_method_icon];
        $posOrderPaymentRepository->setExpenseAccountId($request->expense_account_id)->setMethodDetails($method_details)->createPosOrderPayment($request->amount, $request->pos_order_id,$request->payment_method);
        return api_response($request, true, 200, ['message' => 'Pos Order Payment created successfully']);
    }

    /**
     * @throws UnauthorizedRequestFromExpenseTrackerException
     */
    public function removePosOrderPayment(Request $request, $pos_order_id, PosOrderPaymentRepository $posOrderPaymentRepository)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'expense_account_id' => 'sometimes'
        ]);
        if($request->api_key != config('expense_tracker.api_key'))
            throw new UnauthorizedRequestFromExpenseTrackerException("Unauthorized Request");
        $result = $posOrderPaymentRepository->setExpenseAccountId($request->expense_account_id)->removePosOrderPayment($pos_order_id, $request->amount);
        $message = null;
        if($result) $message = 'Pos Order Payment remove successfully';
        else $message = 'There is no Pos Order Payment';
        return api_response($request, true, 200, ['message' => $message]);
    }
}
