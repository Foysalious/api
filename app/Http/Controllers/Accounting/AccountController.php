<?php

namespace App\Http\Controllers\Accounting;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\AccountRequest;
use App\Sheba\AccountingEntry\Repository\UserAccountRepository;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class AccountController extends Controller
{
    private $accountRepo;

    public function __construct(UserAccountRepository $accountRepo)
    {
        $this->accountRepo = $accountRepo;
    }

    public function getAccountTypeList(Request $request)
    {
        try {
            $response = $this->accountRepo->getAccountType($request->partner->id, $request->all());
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getAccountList(Request $request)
    {
        try {
            $response = $this->accountRepo->getAccounts($request->partner->id, $request->all());
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getCashAccountList(Request $request)
    {
        try {
            $response = $this->accountRepo->getCashAccounts($request->partner->id);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }

    public function createAccount(Request $request)
    {
        try {
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
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }

    public function updateAccount($accountId, Request $request)
    {
        try {
            $this->validate(
                $request,
                [
                    'name' => 'required|string',
                    'name_bn' => 'required|string',
                    'icon' => 'string'
                ]
            );
            $response = $this->accountRepo
                ->setName($request->name)
                ->setNameBn($request->name_bn)
                ->setIcon($request->icon)
                ->updateAccount($accountId, $request->partner->id);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }

    public function deleteAccount($accountId, Request $request)
    {
        try {
            $response = $this->accountRepo->deleteAccount($accountId, $request->partner->id);
            return response()->json(
                [
                    "code" => 200,
                    "message" => $response
                ]
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            if(is_numeric($e->getMessage())) {
                $message = "উপরোক্ত অ্যাকাউন্টটি ". en2bnNumber($e->getMessage())."টি লেনদেনের সাথে জড়িত থাকায় ডিলিট করা সম্ভব নয়।";
            }
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $message]
            );
        }
    }
}