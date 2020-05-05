<?php namespace App\Http\Controllers\Resource;

use App\Http\Controllers\FacebookAccountKit;
use App\Models\Partner;
use App\Models\WithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class ResourceWithdrawalRequestController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'bkash_number' => 'required_if:payment_method,bkash|mobile:bd',
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
        $new_withdrawal = WithdrawalRequest::create(array_merge((new UserRequestInformation($request))->getInformationArray(), [
            'requester_id' => $resource->id,
            'requester_type' => RequesterTypes::RESOURCE,
            'amount' => $request->amount,
            'payment_method' => 'bkash',
            'payment_info' => json_encode(['bkash_number' => $request->bkash_number]),
            'created_by_type' => class_basename($resource),
            'created_by' => $resource->id,
            'created_by_name' => 'Resource - ' . $resource->profile->name,
        ]));

        return api_response($request, $new_withdrawal, 200);
    }
}