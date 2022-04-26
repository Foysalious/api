<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Service\EntriesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Usage\Usage;

class EntriesControllerV2 extends Controller
{
    /** @var EntriesService $entriesRepo */
    private $entriesSvc;

    public function __construct(EntriesService $entriesSvc)
    {
        $this->entriesSvc = $entriesSvc;
    }

    public function details(Request $request, int $entry_id): JsonResponse
    {
        $data = $this->entriesSvc->entryDetails($request->partner, $entry_id);
        return http_response($request, null, 200, ['data' => $data]);
    }

    public function delete(Request $request, int $entry_id): JsonResponse
    {
        $this->entriesSvc->deleteEntry($request->partner, $entry_id);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::ENTRY_DELETE)->create($request->auth_user);
        return http_response($request, null, 200, ['data' => "Entry delete successful"]);
    }
}