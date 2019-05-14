<?php namespace App\Http\Controllers\B2b;


use Illuminate\Validation\ValidationException;
use Sheba\FileManagers\CdnFileManager;
use App\Http\Controllers\Controller;
use App\Models\BusinessSmsTemplate;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class BusinessSmsTemplateController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $business_sms_templates = BusinessSmsTemplate::where('business_id', $business->id)->orderBy('id', 'DESC')->get();
            $sms_templates = [];
            foreach ($business_sms_templates as $sms_template) {
                $template = [
                    'id' => $sms_template->id,
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

    public function show($business, $sms, Request $request)
    {
        try {
            $business = $request->business;
            $sms_template = BusinessSmsTemplate::find((int)$sms);

            $sms_template = [
                    #'event_name' => $sms_template->event_name,
                    #'event_title' => $sms_template->event_title,
                    'template' => $sms_template->template,
                    'variables' => $sms_template->variables,
                    'is_published' => $sms_template->is_published,
                    'cost' => 'BDT 0.25 will be charged per SMS sent.',
                ];

            if (count($sms_template) > 0) return api_response($request, $sms_template, 200, ['sms_template' => $sms_template]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


}