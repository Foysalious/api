<?php


namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class EntriesController extends Controller
{
    /** @var EntriesRepository */
    private $entriesRepo;

    public function __construct(EntriesRepository $entriesRepo) {
        $this->entriesRepo = $entriesRepo;
    }

    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function details(Request $request, $entry_id): JsonResponse
    {
        try {
            $data = $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->entryDetails();
            return api_response($request, null, 200, ['data' => $data]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
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
            $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->deleteEntry();
            return api_response($request, null, 200, ['data' => "Entry delete successful"]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}