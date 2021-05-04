<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\Request;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAccountsTransfer(Request $request){
        $this->validate($request, [
            'amount' => 'required|numeric',
            'from_account_key' => 'required',
            'to_account_key' => 'required',
            'date' => 'required|date_format:Y-m-d H:i:s'
        ]);
        $response = $this->accountingRepo->accountTransfer($request);
        return api_response($request, $response, 200);
    }
}