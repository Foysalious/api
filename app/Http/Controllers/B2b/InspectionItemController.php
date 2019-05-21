<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\InspectionItem\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;

class InspectionItemController extends Controller
{
    use ModificationFields;

    public function edit($business, $inspection, $item, Request $request, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'short_description' => 'required',
                'type' => 'required|string|in:text,radio,number',
                'is_required' => 'required|numeric|in:0,1',
                'instructions' => 'required|string',
            ]);
            $this->setModifier($request->manager_member);
            $inspection_item = $inspection_item_repository->find($item);
            $inspection_item_repository->update($inspection_item, [
                'title' => $request->title,
                'short_description' => $request->short_description,
                'long_description' => $request->instructions,
                'input_type' => $request->type,
                'variables' => json_encode(['is_required' => (int)$request->is_required]),
            ]);
            return api_response($request, $inspection_item, 200);
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

    public function destroy($business, $inspection, $item, Request $request, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $this->setModifier($request->manager_member);
            $inspection_item_repository->delete($item);
            return api_response($request, $inspection, 200);
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

    public function store($business, $inspection, Request $request, Creator $creator, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $this->setModifier($request->manager_member);
            $creator->setData($request->all())->setInspection($inspection_item_repository->find($inspection))->create();
            return api_response($request, $inspection, 200);
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