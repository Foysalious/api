<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Statics\IncomeExpenseStatics;
use Sheba\ModificationFields;
use Sheba\Usage\Usage;

class AccountingController extends Controller
{
    use ModificationFields;

    /** @var AccountingRepository */
    private $accountingRepo;

    public function __construct(AccountingRepository $accountingRepo)
    {
        $this->accountingRepo = $accountingRepo;
    }

    public function storeAccountsTransfer(Request $request): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::transferEntryValidation());
        $request["amount_cleared"] = $request->amount;
        $response = $this->accountingRepo->storeEntry($request, EntryTypes::TRANSFER);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::TRANSFER_ENTRY)->create($request->manager_resource??$request->partner);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function updateAccountsTransfer(Request $request, $transfer_id): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::transferEntryValidation());
        $request["amount_cleared"] = $request->amount;
        $response = $this->accountingRepo->updateEntry($request, EntryTypes::TRANSFER, $transfer_id);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::TRANSFER_ENTRY_UPDATE)->create($request->manager_resource??$request->partner);
        return api_response($request, $response, 200, ['data' => $response]);
    }
}