<?php

namespace App\Http\Controllers\BankUser;


use App\Http\Controllers\Controller;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Throwable;

class BankUserController extends Controller
{
    public function getBankUserInfo(Request $request, ProfileRepository $profileRepository)
    {
        try {
            $request['need_jwt'] = false;
            $info = $profileRepository->getProfileInfo('bankUser', $request->profile, $request);
            $info = array_except($info, 'token');
            return api_response($request, $info, 200, ['data' => $info]);
        }
        catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

}