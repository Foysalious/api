<?php


namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\AccountRepository;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class IncomeController extends Controller
{
    use ModificationFields;

    /** @var AccountRepository */
    private $accountingRepo;

    public function __construct(AccountRepository $accountingRepo) {
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
        $response = $this->accountingRepo->storeExpenseEntry($request);
        return api_response($request, $response, 200);
    }

}