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
use Sheba\Usage\Usage;

class IncomeExpenseController extends Controller
{
    use ModificationFields;

    /** @var AccountingRepository */
    private $accountingRepo;

    public function __construct(AccountingRepository $accountingRepo)
    {
        $this->accountingRepo = $accountingRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function storeIncomeEntry(Request $request): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
        if ($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
            $this->validate($request, ['customer_id' => 'required']);
        }
        $response = $this->accountingRepo->storeEntry($request, EntryTypes::INCOME);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_TRACKER_TRANSACTION)->create($request->auth_user);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param $income_id
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function updateIncomeEntry(Request $request, $income_id): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
        if ($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
            $this->validate($request, ['customer_id' => 'required']);
        }
        $response = $this->accountingRepo->updateEntry($request, EntryTypes::INCOME, $income_id);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_ENTRY_UPDATE)->create($request->auth_user);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function storeExpenseEntry(Request $request): JsonResponse
    {

        $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
        if ($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
            $this->validate($request, ['customer_id' => 'required']);
        }
//            $product = (json_decode($request->inventory_products, true));
        $type = EntryTypes::EXPENSE;
        if ($request->inventory_products && count(json_decode($request->inventory_products, true)) > 0) {
            $type = EntryTypes::INVENTORY;
        }
        $response = $this->accountingRepo->storeEntry($request, $type);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_TRACKER_TRANSACTION)->create($request->auth_user);
        return api_response($request, $response, 200, ['data' => $response]);

    }

    /**
     * @param Request $request
     * @param $expense_id
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function updateExpenseEntry(Request $request, $expense_id): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::incomeExpenseEntryValidation());
        if ($request->has("amount_cleared") && $request->amount > $request->amount_cleared) {
            $this->validate($request, ['customer_id' => 'required']);
        }
        $type = EntryTypes::EXPENSE;
        if ($request->has('inventory_products') && count(json_decode($request->inventory_products, true))) {
            $type = EntryTypes::INVENTORY;
        }
        $response = $this->accountingRepo->updateEntry($request, $type, $expense_id);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_ENTRY_UPDATE)->create($request->auth_user);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function getTotalIncomeExpense(Request $request): JsonResponse
    {
        $this->validate($request, IncomeExpenseStatics::totalIncomeExpenseValidation());
        $response = $this->accountingRepo->getAccountsTotal($request);
        return api_response($request, $response, 200, ['data' => $response]);

    }

}