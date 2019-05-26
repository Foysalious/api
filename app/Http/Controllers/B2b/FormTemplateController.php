<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Validation\ValidationException;
use Sheba\Business\FormTemplate\Creator;
use Sheba\ModificationFields;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;


class FormTemplateController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $form_templates = FormTemplate::where('owner_id', $business->id)->published()->orderBy('id', 'DESC')->get();
            $templates = [];
            foreach ($form_templates as $template) {

                $template = [
                    'id' => $template->id,
                    'title' => $template->title,
                    'short_description' => $template->short_description,
                ];
                array_push($templates, $template);
            }
            if (count($templates) > 0) return api_response($request, $templates, 200, ['templates' => $templates]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'short_description' => 'required',
                'variables' => 'required|string',
            ]);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('form_template.rw')) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $form_template = $creator->setData($request->all())->setOwner($request->business)->create();
            return api_response($request, null, 200, ['id' => $form_template->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function get($business, $form_template, Request $request, FormTemplateRepositoryInterface $formTemplateRepository)
    {
        try {
            $form_template = $formTemplateRepository->find($form_template);
            if (!$form_template) return api_response($request, null, 404);
            $items = $inspections = [];
            foreach ($form_template->items as $item) {
                array_push($items, [
                    'id' => $item->id,
                    'title' => $item->title,
                    'short_description' => $item->short_description,
                    'instructions' => $item->long_description,
                    'type' => $item->input_type,
                    'is_required' => (int)json_decode($item->variables)->is_required,
                ]);
            }
            foreach ($form_template->inspections as $inspection) {
                array_push($inspections, [
                    'id' => $inspection->id,
                    'type' => config('business.INSPECTION_TYPE')[$inspection->type],
                    'schedule_date' => $inspection->start_date->format('jS M, Y'),
                ]);
            }
            $data = [
                'id' => $form_template->id,
                'title' => $form_template->title,
                'short_description' => $form_template->short_description,
                'created_at' => $form_template->created_at->toDateTimeString(),
                'items' => $items,
                'inspections' => $inspections
            ];
            return api_response($request, null, 200, ['form_template' => $data]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function edit($business, $form_template, Request $request, AccessControl $access_control, FormTemplateRepositoryInterface $formTemplateRepository)
    {
        try {
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('form_template.rw')) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $form_template = $formTemplateRepository->find($form_template);
            $formTemplateRepository->update($form_template, [
                'title' => $request->title,
                'short_description' => $request->short_description,
            ]);
            return api_response($request, $form_template, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}