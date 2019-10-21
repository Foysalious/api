<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Sheba\Business\Bid\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\ProcurementRepository;

class BidController extends Controller
{
    use ModificationFields;

    public function store($partner, Request $request, Creator $creator, ProcurementRepository $procurement_repository)
    {
        try {
            $this->validate($request, [
                'procurement_id' => 'required|numeric',
                'items' => 'required|string',
            ]);
            $this->setModifier($request->manager_resource);
            /** @var Procurement $procurement */
            $procurement = $procurement_repository->find($request->procurement_id);
            $procurement->load('items.fields');
            $items = collect(json_decode($request->items));
            $field_results = [];
            foreach ($procurement->items as $procurement_item) {
                $item = $items->where('id', $procurement_item->id)->first();
                $fields = collect($item->fields);
                foreach ($procurement_item->fields as $item_field) {
                    $field = $fields->where('id', $item_field->id)->first();
                    $variables = json_decode($item_field->variables);
                    if ((int)$variables->is_required && !$field) return api_response($request, null, 400, ['message' => $item_field->title . ' missing']);
                    array_push($field_results, $field);
                }
            }
            $bid = $creator->setBidder($request->partner)->setProcurement($procurement)->setStatus('pending')->setFieldResults($field_results)->create();
            return api_response($request, null, 200, ['bid' => $bid->id]);
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
}