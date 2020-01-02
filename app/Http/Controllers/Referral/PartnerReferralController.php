<?php namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\PartnerReferral;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Referral\Referrals;
use Sheba\ModificationFields;
use Throwable;


class PartnerReferralController extends Controller
{
    use ModificationFields;
    public function index(Request $request)
    {
       try{
           $partner  = $request->partner;
           $reference    = Referrals::getReference($partner);
           $referrals=$reference->getReferrals();
           return api_response($request,$reference->refers, 200,['data'=>$referrals]);
       }catch (\Throwable $e){
           dd($e);
       }
    }

    public function setReference() { }

    public function referLinkGenerate() { }

    public function earnings() { }

    public function details() { }

    public function store(Request $request){
        try{
            $this->validate($request, [
                'name'          => 'required|string',
                'mobile'       => 'required|string',
            ]);
            $this->setModifier($request->manager_resource);

            $partner_referral_data = [
                'partner_id' => $request->partner->id,
                'resource_name' => $request->name,
                'resource_mobile' => $request->mobile,
                'company_name' => $request->name,
            ];

            PartnerReferral::create($this->withCreateModificationField($partner_referral_data));
            return api_response($request, null, 200);

        }catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
