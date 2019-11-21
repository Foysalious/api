<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\ShebaUser;

class UserController extends Controller
{

    public function show(Request $request, ShebaUser $sheba_user)
    {
        try {
            $sheba_user->setUser($request->user);
            $data = [
                'name' => $sheba_user->getName(),
                'image' => $sheba_user->getImage(),
                'balance' => $sheba_user->getWallet(),
                'mobile' => $sheba_user->getMobile(),
                'type' => strtolower(class_basename($request->user)),
                'type_id' => $request->user->id
            ];
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
