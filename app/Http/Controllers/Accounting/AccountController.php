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
                    'icon' => 'nullable'
                ]
            );

            $response = $this->accountRepo
                ->setName($request->name)
                ->setNameBn($request->name_bn)
                ->setRootAccount($request->root_account)
                ->setAccountType($request->account_type)
                ->setIcon($request->icon)
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
                    'icon' => 'nullable',
                    'account_type' => 'required|string',
                ]
            );
            $response = $this->accountRepo
                ->setName($request->name)
                ->setNameBn($request->name_bn)
                ->setAccountType($request->account_type)
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
}