<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class IncomeExpenseController extends Controller
{
    use ModificationFields;

    /** @var AccountingRepository */
    private $accountingRepo;

    public function __construct(AccountingRepository $accountingRepo) {
        $this->accountingRepo = $accountingRepo;
    }

    public function storeIncomeEntry(Request $request) {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'from_account_key' => 'required',
            'to_account_key' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'amount_cleared' => 'sometimes|required|numeric',
            'customer_id' => 'required_with:amount_cleared'
        ]);
        $response = $this->accountingRepo->storeEntry($request, EntryTypes::INCOME);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    public function storeExpenseEntry(Request $request) {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'from_account_key' => 'required',
            'to_account_key' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'amount_cleared' => 'sometimes|required|numeric',
            'customer_id' => 'required_with:amount_cleared'
        ]);
        $response = $this->accountingRepo->storeEntry($request, EntryTypes::EXPENSE);
        return api_response($request, $response, 200, ['data' => $response]);
    }

}