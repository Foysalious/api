<?php namespace App\Http\Controllers\InfoCall;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\InfoCall\InfoCall;
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
                'location_id' => 'required|numeric',
            ]);
            if ($request->has('location_id')) {
                $location = Location::find($request->location_id);
                if ($location == null) return api_response($request, null, 404, ['message' => 'Location Not Found']);;
            }
            $profile_exists = Profile::select('id', 'name', 'address')->where('mobile', 'like', '%'.$request->mobile.'%')->get()->toArray();
            if ($profile_exists) {
                $customer = Customer::where('profile_id', $profile_exists[0]['id'])->get();
                $this->setModifier($customer[0]);
                $profile = $customer[0]->profile;
                $data = [
                'service_name' => $request->service_name,
                'customer_name' => $profile->name,
                'location_id' => $request->location_id,
                'customer_mobile' => $request->mobile,
                'customer_email' => !empty($profile->email) ? $profile->email : null,
                'customer_address' => !empty($profile->address) ? $profile->address : '',
                'status'=> Statuses::OPEN,
                    'portal_name' => 'customer-app',
                'follow_up_date' => Carbon::now()->addMinutes(30),
                'intended_closing_date' => Carbon::now()->addMinutes(30)
            ];

                $info_call = $customer[0]->infoCalls()->create($this->withCreateModificationField($data));
                $this->sendNotificationToSD($info_call);
            }
            else {
                $info_call =   InfoCall::create([
                'customer_mobile'=>$request->mobile,
                'is_customer_vip'=>0,
                'priority'=>'Low',
                'flag'=>'Green',
                'info_category'=>'not_available',
                'status'=> Statuses::OPEN,
                'service_name' => $request->service_name,
                'created_by'=>0,
                'created_by_name'=>'Guest User',
                'updated_by'=>0,
                'updated_by_name'=>'Guest User',
                    'portal_name' => 'customer-app',
                 'follow_up_date'=> Carbon::now()->addMinutes(30),
                    'intended_closing_date' => Carbon::now()->addMinutes(30)
            ]);
                $this->sendNotificationToSD($info_call);
            }
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
