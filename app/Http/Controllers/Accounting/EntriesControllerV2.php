<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Usage\Usage;

class EntriesControllerV2 extends Controller
{
    /** @var EntriesRepository $entriesRepo */
    private $entriesRepo;

    public function __construct(EntriesRepository $entriesRepo)
    {
        $this->entriesRepo = $entriesRepo;
    }

    public function details(Request $request, int $entry_id): JsonResponse
    {
        $data = $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->entryDetails();
        return http_response($request, null, 200, ['data' => $data]);
    }

    public function delete(Request $request, int $entry_id): JsonResponse
    {
        $this->entriesRepo->setPartner($request->partner)->setEntryId($entry_id)->deleteEntry();
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::ENTRY_DELETE)->create($request->auth_user);
        return http_response($request, null, 200, ['data' => "Entry delete successful"]);
    }
}