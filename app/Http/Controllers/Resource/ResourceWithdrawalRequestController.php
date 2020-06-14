<?php namespace App\Http\Controllers\Resource;

use App\Models\WithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;
use Sheba\Resource\WithdrawalRequest\Creator;

class ResourceWithdrawalRequestController extends Controller
{
    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'bkash_number' => 'required|mobile:bd',
        ]);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        /**
         * Limit Validation
         */
        $limitBkash = constants('WITHDRAW_LIMIT')['bkash'];
        if (((double)$request->amount < $limitBkash['min'] || (double)$request->amount > $limitBkash['max'])) {
            return api_response($request, null, 400, ['message' => 'Payment Limit mismatch for bkash minimum limit ' . $limitBkash['min'] . ' TK and maximum ' . $limitBkash['max'] . ' TK']);
        }
        $allowed_to_send_request = $resource->isAllowedToSendWithdrawalRequest();
        if (!$allowed_to_send_request) {
            if (!$allowed_to_send_request) $message = "You have already sent a Withdrawal Request";
            else $message = "You don't have sufficient balance";
            return api_response($request, null, 403, ['message' => $message]);
        }
        $userRequestInformation = (new UserRequestInformation($request))->getInformationArray();
        $new_withdrawal = $creator->setResource($resource)
            ->setRequesterType(RequesterTypes::RESOURCE)
            ->setAmount($request->amount)
            ->setPaymentMethod('bkash')
            ->setBkashNumber($request->bkash_number)
            ->setRequestUserInformation($userRequestInformation)
            ->create();

        return api_response($request, $new_withdrawal, 200);
    }
}