<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\UserAccountRepository;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private $accountRepo;

    public function __construct(UserAccountRepository $accountRepo)
    {
        $this->accountRepo = $accountRepo;
    }

    public function getAccountTypeList(Request $request)
    {
        $response = $this->accountRepo->getAccountType($request->all());
        return api_response($request, $response, 200, ['data' => $response]);
    }

    public function getAccountList(Request $request)
    {
        $response = $this->accountRepo->getAccounts($request->all());
        return api_response($request, $response, 200, ['data' => $response]);
    }
}