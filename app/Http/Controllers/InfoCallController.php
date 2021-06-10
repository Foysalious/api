<?php namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use mysql_xdevapi\Exception;
use Sheba\Dal\InfoCall\Statuses;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Dal\InfoCall\InfoCall;

class InfoCallController extends Controller
{
    use ModificationFields;

    public function index($customer, Request $request)
    {
        $customer = $request->customer;
        $info_calls = $customer->infoCalls()->orderBy('created_at', 'DESC')->get();
        $info_calls = $info_calls->filter(function ($info_call) {
            return is_null($info_call->order);
        });
        $info_call_lists = collect([]);
        foreach ($info_calls as $info_call) {
            $info = [
                'id' => $info_call->id,
                'code' => $info_call->code(),
                'service_name' => $info_call->service_name,
                'status' => $info_call->status,
                'created_at' => $info_call->created_at->format('F j, Y'),
            ];
            $info_call_lists->push($info);
        }
        return api_response($request, $info_call_lists, 200, ['info_call_lists' => $info_call_lists]);
    }

    public function getDetails($customer, $info_call, Request $request)
    {
        $customer = $request->customer;
        $info_call = InfoCall::find($info_call);
        $details = [
            'id' => $info_call->id,
            'code' => $info_call->code(),
            'service_name' => $info_call->service_name,
            'status' => $info_call->status,
            'created_at' => $info_call->created_at->format('F j, h:ia'),
            'estimated_budget' => $info_call->estimated_budget,
        ];

        return api_response($request, $details, 200, ['details' => $details]);
    }

    public function store($customer, Request $request)
    {
        $this->setModifier($request->customer);
        $this->validate($request, [
            'service_name' => 'required|string',
            'estimated_budget' => 'numeric',
            'location_id' => 'numeric',
        ]);
        $customer = $request->customer;
        $profile = $customer->profile;

        $data = [
            'service_name' => $request->service_name,
            'estimated_budget' => $request->estimated_budget,
            'customer_name' => $profile->name,
            'location_id' => $request->location_id,
            'customer_mobile' => $profile->mobile,
            'customer_email' => !empty($profile->email) ? $profile->email : null,
            'customer_address' => !empty($profile->address) ? $profile->address : '',
            'status'=> Statuses::OPEN,
            'follow_up_date' => Carbon::now()->addMinutes(30),
            'intended_closing_date' => Carbon::now()->addMinutes(30)
        ];

        $info_call = $customer->infoCalls()->create($this->withCreateModificationField($data));
        try {
            $this->sendNotificationToSD($info_call);
        } catch (Exception $e){
            logError($e);
        }
        return api_response($request, 1, 200);
    }

    private function sendNotificationToSD($info_call)
    {
        $sd_but_not_crm = User::where('department_id', 5)->where('is_cm', 0)->pluck('id');
        notify()->users($sd_but_not_crm)->send([
            "title" => 'New Info Call Created by Customer',
            'link' => config('sheba.admin_url') . '/info-call/' . $info_call->id,
            "type" => notificationType('Info')
        ]);
    }

}
