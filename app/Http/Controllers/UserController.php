<?php namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\ShebaUser;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param ShebaUser $sheba_user
     * @return JsonResponse
     */
    public function show(Request $request, ShebaUser $sheba_user)
    {
        $sheba_user->setUser($request->user);
        $data = [
            'name' => $sheba_user->getName(),
            'email' => $sheba_user->getEmail(),
            'image' => $sheba_user->getImage(),
            'balance' => $sheba_user->getWallet(),
            'mobile' => $sheba_user->getMobile(),
            'topup_prepaid_max_limit' => $sheba_user->getTopUpPrepaidMaxLimit(),
            'type' => strtolower(class_basename($request->user)),
            'type_id' => $request->user->id
        ];

        return api_response($request, $data, 200, ['data' => $data]);
    }
}
