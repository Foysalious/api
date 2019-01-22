<?php namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerOnBoardModerationRequest;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Sheba\LightOnBoarding\PartnerModerator;

class LitePartnerOnBoardingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $affiliate = $request->affiliate->load('onboardedPartners.resources.profile');
            $partners = $affiliate->onboardedPartners->map(function (Partner $partner) {
                $resource = $partner->getFirstAdminResource();
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'resource' => !$resource ? null : [
                        'id' => $resource->id,
                        'name' => $resource->profile->name,
                        'mobile' => $resource->profile->mobile,
                    ],
                    'moderation_status' => $partner->moderation_status,
                    'income' => $partner->moderation_status == 'approved' ? constants('AFFILIATION_LITE_ONBOARD_REWARD') : 0,
                    'created_at' => $partner->created_at->toDateTimeString()
                ];
            });
            return api_response($request, $partners, 200, ['partners' => $partners]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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