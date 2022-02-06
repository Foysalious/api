<?php


namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Usage\Usage;

class EntriesController extends Controller
{
    /** @var EntriesRepository */
    private $entriesRepo;

    public function __construct(EntriesRepository $entriesRepo)
    {
        $this->entriesRepo = $entriesRepo;
    }

    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function details(Request $request, $entry_id): JsonResponse
    {
        $data = $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->entryDetails();
        return api_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param $entry_id
     * @return JsonResponse
     */
    public function delete(Request $request, $entry_id): JsonResponse
    {

        $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->deleteEntry();
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::ENTRY_DELETE)->create($request->auth_user);
        return api_response($request, null, 200, ['data' => "Entry delete successful"]);
    }
}