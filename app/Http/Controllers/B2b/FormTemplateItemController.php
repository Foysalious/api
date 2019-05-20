<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\FormTemplateItemRepository;

class FormTemplateItemController extends Controller
{
    use ModificationFields;

    public function edit($business, $form_template, $item, Request $request, FormTemplateItemRepository $form_template_item_repository)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'short_description' => 'required',
                'input_type' => 'required|string|in:text,radio,number',
                'is_required' => 'required|numeric|in:0,1',
            ]);
            $this->setModifier($request->manager_member);
            $form_template_item = $form_template_item_repository->find($item);
            $form_template_item_repository->update($form_template_item, [
                'title' => $request->title,
                'short_description' => $request->short_description,
                'long_description' => $request->instruction,
                'input_type' => $request->input_type,
                'variables' => json_encode(['is_required' => (int)$request->is_required]),
            ]);
            return api_response($request, $form_template, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}