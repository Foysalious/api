<?php namespace App\Http\Controllers\B2b;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Business\ApprovalSetting\ApprovalSettingRequest;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSettingModule\Modules;
use Sheba\Dal\ApprovalSetting\Targets;

class ApprovalSettingsController extends Controller
{
    public function index(Request $request)
    {
       $approval_settings =  ApprovalSetting::where('business_id', $request->business->id)->get();
    }

    public function store(Request $request, ApprovalSettingRequest $approval_setting_request)
    {
        try {
            $this->validate($request, [
                'modules' => 'required|in:' . implode(',', Modules::get()),
                'note' => 'required|string',
                'target_type' => 'required|in:' . implode(',', Targets::get()),
                'approvers' => 'required',
            ]);
            $approval_setting_request->setModules($request->modules)
                ->setTargetType($request->target_type)
                ->setTargetId($request->targetId)->setNote($request->note)->setApprovers($request->appovers);
        }catch (\Exception $e){
            dd($e);
        }
    }
}
