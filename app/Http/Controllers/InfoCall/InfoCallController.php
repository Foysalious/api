<?php namespace App\Http\Controllers\InfoCall;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\InfoCall\Statuses;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class InfoCallController extends Controller
{
    use ModificationFields;

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'service_name' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'location_id' => 'numeric',
            ]);
            $profile = Profile::where('mobile', 'like', '%'.$request->mobile.'%')->get()->toArray();
//            dd(!$profile);
//            $data = [
//                'service_name' => $request->service_name,
//                'customer_name' => $profile? $profile->name : null,
//                'location_id' => $request->location_id,
//                'customer_mobile' => $request->mobile,
//                'customer_email' => $profile ? $profile->email : null,
//                'customer_address' => $profile ? $profile->address : '',
//                'status'=> Statuses::OPEN,
//                'follow_up_date' => Carbon::now()->addMinutes(30),
//                'intended_closing_date' => Carbon::now()->addMinutes(30)
//            ];
//            $info_call = $customer->infoCalls()->create($this->withCreateModificationField($data));
//            $this->sendNotificationToSD($info_call);
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, $e, 500);
        }
    }

    public function sendNotificationToSD($info_call)
    {
        try {
            $sd_but_not_crm = User::where('department_id', 5)->where('is_cm', 0)->pluck('id');
            notify()->users($sd_but_not_crm)->send([
                "title" => 'New Info Call Created by User',
                'link' => config('sheba.admin_url') . '/info-call/' . $info_call->id,
                "type" => notificationType('Info')
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }


}
