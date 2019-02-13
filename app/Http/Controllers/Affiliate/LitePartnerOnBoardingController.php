<?php namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerOnBoardModerationRequest;
use App\Models\Partner;
use App\Repositories\AffiliateRepository;
use Illuminate\Http\Request;
use App\Sheba\LightOnBoarding\PartnerModerator;

class LitePartnerOnBoardingController extends Controller
{
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $affiliate = $request->affiliate->load(['onboardedPartners' => function ($q) use ($offset, $limit) {
                $q->offset($offset)->limit($limit)->with('resources.profile')->orderBy('created_at', 'desc');
            }]);
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

    public function rejectReason(Request $request)
    {
        $reasons = constants('LITE_PARTNER_REJECT_REASON');
        return api_response($request, $reasons, 200, ['reasons' => $reasons]);
    }

    public function litePartners(PartnerOnBoardModerationRequest $request, AffiliateRepository $repo)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $affiliate = $repo->moderatedPartner($request, 'pending');
            $source = ['lat' => $request->get('lat'), 'lng' => $request->get('lng')];
            $partners = $affiliate->onboardedPartners->map(function (Partner $partner) use ($repo, $source) {
                return $repo->mapForModerationApi($partner, $source);
            })->sortByDesc('distance')->forPage(($offset - 1), $limit)->values();
            return api_response($request, $partners, 200, ['partners' => $partners]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function history(Request $request, AffiliateRepository $repo)
    {
        try {
            $affiliate = $repo->moderatedPartner($request);
            $partners = $affiliate->onboardedPartners->map(function (Partner $partner) use ($repo) {
                return $repo->mapForModerationApi($partner);
            });
            return api_response($request, $partners, 200, ['partners' => $partners]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function litePartnerDetails(Request $request, AffiliateRepository $repo)
    {
        try {
            $this->validate($request, [
                'partner_id' => 'required|numeric'
            ]);
            $partner = Partner::find($request->partner_id);
            $affiliate = $repo->moderatedPartner($request, 'pending');

            if (!is_null($partner)) {
                if($affiliate->id == $partner->moderator_id) {
                    $partner = $repo->mapForModerationApi($partner, null , true);
                    return api_response($request, $partner, 200, ['name' => $partner]);
                }
                else api_response($request, [], 403, ['message' => 'Partner is not moderated by you.']);
            }
            return api_response($request, [], 404, ['message' => 'Partner not found.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
