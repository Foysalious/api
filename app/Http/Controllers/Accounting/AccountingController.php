<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\AccountRepository;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class AccountingController extends Controller
{
    use ModificationFields;

    /** @var AccountRepository */
    private $accountingRepo;

    public function __construct(AccountRepository $accountingRepo) {
        $this->accountingRepo = $accountingRepo;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAccountsTransfer(Request $request){
        $this->validate($request, [
            'amount' => 'required|numeric',
            'from_account_key' => 'required',
            'to_account_key' => 'required',
            'date' => 'required|date_format:Y-m-d'
        ]);
        $response = $this->accountingRepo->accountTransfer($request);
        return api_response($request, $response, 200);
    }
}