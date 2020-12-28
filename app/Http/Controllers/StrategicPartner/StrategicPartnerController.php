<?php


namespace App\Http\Controllers\StrategicPartner;


use App\Http\Controllers\Controller;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;

class StrategicPartnerController extends Controller
{
    public function getStrategicPartnerInfo(Request $request, ProfileRepository $profileRepository)
    {
        $info = $profileRepository->getProfileInfo('strategicPartnerMember', $request->access_token->authorizationRequest->profile, $request);
        return api_response($request, $info, 200, ['data' => $info]);
    }

}