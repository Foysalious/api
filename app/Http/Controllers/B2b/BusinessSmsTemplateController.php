<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessSmsTemplate;
use App\Models\BusinessTrip;
use App\Models\Driver;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use DB;

class BusinessSmsTemplateController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $business_sms_templates = BusinessSmsTemplate::where('business_id', $business->id)->orderBy('id', 'DESC')->get();
            #dd($sms_templates);
            $sms_templates = [];
            foreach ($business_sms_templates as $sms_template) {
                $template = [
                    'event_name' => $sms_template->event_name,
                    'event_title' => $sms_template->event_title,
                    'template' => $sms_template->template,
                    'variables' => $sms_template->variables,
                    'is_published' => $sms_template->is_published,
                ];

                array_push($sms_templates, $template);
            }

            if (count($sms_templates) > 0) return api_response($request, $sms_templates, 200, ['sms_templates' => $sms_templates]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($business, $sms, Request $request)
    {
        try {
            $this->validate($request, [
                'is_published' => 'required|boolean',
                'template' => 'required|string',
            ]);
            $sms_template = BusinessSmsTemplate::find((int)($sms));

            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $data = [
                'is_published' => $request->is_published,
                'template' => $request->template,
            ];
            $sms_template->update($this->withUpdateModificationField($data));
            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}