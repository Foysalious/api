<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerOnBoardModerationRequest;
use App\Sheba\LightOnBoarding\PartnerModerator;

class PartnerOnBoardingController extends Controller
{
    public function __construct()
    {
    }

    public function acceptRequest(PartnerOnBoardModerationRequest $request, $affiliate, $partner_id)
    {
        try {
            $data = $request->all();
            $moderator = new PartnerModerator('moderator');
            $moderator->setModerator($request->affiliate)->setPartner($partner_id)->accept($data);
            return api_response($request, null, 200, ['message' => 'Partner is being accepted successfully']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function rejectRequest(PartnerOnBoardModerationRequest $request, $affiliate, $partner_id)
    {
        try {
            $data = $request->all();
            $moderator = new PartnerModerator('moderator');
            $moderator->setModerator($request->affiliate)->setPartner($partner_id)->reject($data);
            return api_response($request, null, 200, ['message' => 'Partner is being rejected successfully']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }
}