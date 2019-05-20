<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
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

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'short_description' => 'required',
                'variables' => 'required|string',
            ]);
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
            $items = [];
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
            $data = [
                'id' => $form_template->id,
                'title' => $form_template->title,
                'short_description' => $form_template->short_description,
                'created_at' => $form_template->created_at->toDateTimeString(),
                'items' => $items,
                'inspections' => [
                    [
                        'id' => 1,
                        'type' => 'text',
                        'schedule_date' => '14th March,2010'
                    ],
                    [
                        'id' => 1,
                        'type' => 'text',
                        'schedule_date' => '14th March,2010'
                    ]
                ]
            ];
            return api_response($request, null, 200, ['form_template' => $data]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function edit($business, $form_template, Request $request, FormTemplateRepositoryInterface $formTemplateRepository)
    {
        try {
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