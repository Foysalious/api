<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Statics\IncomeExpenseStatics;
use Sheba\ModificationFields;

class AccountingController extends Controller
{
    use ModificationFields;

    /** @var AccountingRepository */
    private $accountingRepo;

    public function __construct(AccountingRepository $accountingRepo) {
        $this->accountingRepo = $accountingRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeAccountsTransfer(Request $request): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::transferEntryValidation());
            $request["amount_cleared"] = $request->amount;
            $response = $this->accountingRepo->storeEntry($request, EntryTypes::TRANSFER);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }
}