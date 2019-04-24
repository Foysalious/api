<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessJoinRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use DB;
use Sheba\Sms\Sms;

class BusinessesController extends Controller
{
    use ModificationFields;
    private $sms;
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function inviteVendors($business, Request $request)
    {
        try {
            $this->validate($request, [
                'numbers' => 'required|array'
            ]);
            $business = $request->business;
            $this->setModifier($business);

            $numbers = explode(', ', $request['numbers'][0]);
            foreach ($numbers as $number){
                $data = [
                    'business_id' => $business->id,
                    'mobile' =>$number
                ];
                BusinessJoinRequest::create($data);
                $this->sms->shoot($number, "Nothing");
            }
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}