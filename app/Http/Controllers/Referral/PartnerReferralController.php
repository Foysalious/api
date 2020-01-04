<?php namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\Referrals;
use Throwable;

class PartnerReferralController extends Controller
{

    public function index(Request $request, Referrals $referrals)
    {
        try {
            $partner   = $request->partner;
            $reference = $referrals::getReference($partner);
            $referrals = $reference->getReferrals();
            return api_response($request, $reference->refers, 200, ['data' => $referrals]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function setReference() { }

    public function referLinkGenerate() { }

    public function earnings() { }

    public function details() { }

    public function store(Request $request, Referrals $referrals)
    {
        try {
            $this->validate($request, [
                'name'   => 'required|string',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $referrals::getReference($request->partner)->store($request);
            return api_response($request, null, 200);

        } catch (AlreadyReferred $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (AlreadyExistProfile $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
