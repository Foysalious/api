<?php

namespace App\Http\Controllers\Accounting;

use App\Exceptions\DoNotReportException;
use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\UserAccountRepository;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use \Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    private $accountRepo;

    public function __construct(UserAccountRepository $accountRepo)
    {
        $this->accountRepo = $accountRepo;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getAccountTypeList(Request $request): JsonResponse
    {
        $response = $this->accountRepo->getAccountType($request->partner->id, $request->all());
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getAccountList(Request $request): JsonResponse
    {
        $response = $this->accountRepo->getAccounts($request->partner->id, $request->all());
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getCashAccountList(Request $request): JsonResponse
    {
        $response = $this->accountRepo->getCashAccounts($request->partner->id);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function createAccount(Request $request): JsonResponse
    {
        $this->validate(
            $request,
            [
                'name' => 'required|string',
                'name_bn' => 'required|string',
                'root_account' => 'required|string',
                'account_type' => 'required|string',
                'icon' => 'string',
                'opening_balance' => 'numeric',
                'balance_type' => 'required_with:opening_balance|in:positive,negative'
            ]
        );

        $response = $this->accountRepo
            ->setName($request->name)
            ->setNameBn($request->name_bn)
            ->setRootAccount($request->root_account)
            ->setAccountType($request->account_type)
            ->setIcon($request->icon)
            ->setOpeningBalance($request->opening_balance)
            ->setBalanceType($request->balance_type)
            ->storeAccount($request->partner->id);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param $accountId
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAccount($accountId, Request $request): JsonResponse
    {
        $this->validate(
            $request,
            [
                'name'            => 'required|string',
                'name_bn'         => 'required|string',
                'opening_balance' => 'required|numeric',
                'icon'            => 'string'
            ]
        );
        $response = $this->accountRepo
            ->setName($request->name)
            ->setNameBn($request->name_bn)
            ->setIcon($request->icon)
            ->setOpeningBalance($request->opening_balance)
            ->setBalanceType($request->balance_type)
            ->updateAccount($accountId, $request->partner->id);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @throws DoNotReportException
     * @throws AccountingEntryServerError
     */
    public function deleteAccount($accountId, Request $request): JsonResponse
    {
        $response = $this->accountRepo->deleteAccount($accountId, $request->partner->id);
        if (is_numeric($response)) {
            throw new DoNotReportException("উপরোক্ত অ্যাকাউন্টটি " . en2bnNumber($response) . "টি লেনদেনের সাথে জড়িত থাকায় ডিলিট করা সম্ভব নয়।", 403);
        }
        return api_response($request, $response, 200, ['data' => $response]);
    }
}