<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Statics\IncomeExpenseStatics;
use Sheba\ModificationFields;

class IncomeExpenseController extends Controller
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
    public function storeIncomeEntry(Request $request): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
            if($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
                $this->validate($request, ['customer_id' => 'required']);
            }
            $response = $this->accountingRepo->storeEntry($request, EntryTypes::INCOME);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param $income_id
     * @return JsonResponse
     */
    public function updateIncomeEntry(Request $request, $income_id): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
            if($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
                $this->validate($request, ['customer_id' => 'required']);
            }
            $response = $this->accountingRepo->updateEntry($request, EntryTypes::INCOME, $income_id);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeExpenseEntry(Request $request): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
            if($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
                $this->validate($request, ['customer_id' => 'required']);
            }
//            $product = (json_decode($request->inventory_products, true));
            $type = count(json_decode($request->inventory_products, true)) ? EntryTypes::INVENTORY : EntryTypes::EXPENSE;
            $response = $this->accountingRepo->storeEntry($request, $type);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param $expense_id
     * @return JsonResponse
     */
    public function updateExpenseEntry(Request $request, $expense_id): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
            if($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
                $this->validate($request, ['customer_id' => 'required']);
            }
//            $product = (json_decode($request->inventory_products, true));
            $type = count(json_decode($request->inventory_products, true)) ? EntryTypes::INVENTORY : EntryTypes::EXPENSE;
            $response = $this->accountingRepo->updateEntry($request, $type, $expense_id);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotalIncomeExpense(Request $request): JsonResponse
    {
        try {
            $this->validate($request, IncomeExpenseStatics::totalIncomeExpenseValidation());
            $response = $this->accountingRepo->getAccountsTotal($request);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (ValidationException $exception) {
            return api_response($request, null, 400, ['message' => $exception->getMessage()]);
        } catch (AccountingEntryServerError $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

}