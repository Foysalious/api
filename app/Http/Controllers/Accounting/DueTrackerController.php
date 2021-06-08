<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\ModificationFields;
use Sheba\Reports\PdfHandler;
use Sheba\Usage\Usage;
use Exception;

class DueTrackerController extends Controller
{
    use ModificationFields;

    /** @var DueTrackerRepository */
    private $dueTrackerRepo;

    public function __construct(DueTrackerRepository $dueTrackerRepo) {
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request ): JsonResponse
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
                'entry_type' => 'required|in:due,deposit',
                'account_key' => 'required',
                'customer_id' => 'required|integer',
                'date' => 'required|date_format:Y-m-d H:i:s'
            ]);
            $request->merge(['customer_id' => $request->customer_id]);
            $response = $this->dueTrackerRepo->storeEntry($request, $request->entry_type);
            (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->auth_user);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }


    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function update(Request $request, $entry_id ): JsonResponse
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
                'entry_type' => 'required|in:due,deposit',
                'account_key' => 'required',
                'customer_id' => 'required|integer',
                'date' => 'required|date_format:Y-m-d H:i:s'
            ]);
            $request->merge(['entry_id' => $entry_id]);
            $response = $this->dueTrackerRepo->storeEntry($request, $request->entry_type, true);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function delete(Request $request, $entry_id): JsonResponse
    {
        try {
            $this->dueTrackerRepo->setPartner($request->partner)->setEntryId($entry_id)->deleteEntry();
            return api_response($request, null, 200, ['data' => "Entry delete successful"]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function details(Request $request, $entry_id): JsonResponse
    {
        try {
            $data = $this->dueTrackerRepo->setPartner($request->partner)->setEntryId($entry_id)->entryDetails();
            return api_response($request, null, 200, ['data' => $data]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function dueList(Request $request): JsonResponse
    {
        try {
            $data = $this->dueTrackerRepo->setPartner($request->partner)->getDueList($request);
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
            return api_response($request, null, 200, ['data' => $data]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     */
    public function dueListByCustomerId(Request $request, $customerId): JsonResponse
    {
        try {
            $data = $this->dueTrackerRepo->setPartner($request->partner)->getDueListByCustomer($request, $customerId);

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
            return api_response($request, null, 200, ['data' => $data]);
        } catch (Exception $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }
}