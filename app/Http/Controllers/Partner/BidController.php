<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Sheba\Business\Bid\Updater;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Sheba\Business\Bid\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\ProcurementRepository;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;

class BidController extends Controller
{
    use ModificationFields;

    public function store($partner, Request $request, Creator $creator, Updater $updater, ProcurementRepository $procurement_repository, BidRepositoryInterface $bid_repository)
    {
        try {
            $this->validate($request, [
                'procurement_id' => 'required|numeric',
                'items' => 'required|string',
                'status' => 'required|string|in:sent,pending',
                'price' => 'sometimes|numeric',
                'proposal' => 'required|string',
            ]);
            $this->setModifier($request->manager_resource);
            $bid = $bid_repository->where('procurement_id', $request->procurement_id)->where('bidder_type', 'like', '%Partner')
                ->where('bidder_id', $request->partner->id)->first();
            if ($bid) {
                $items = collect(json_decode($request->items));
                $field_results = [];
                foreach ($bid->items as $procurement_item) {
                    $item = $items->where('id', $procurement_item->id)->first();
                    foreach ($procurement_item->fields as $item_field) {
                        $variables = json_decode($item_field->variables);
                        $required = (int)$variables->is_required;
                        if ($required && !$item) return api_response($request, null, 400, ['message' => $procurement_item->type . ' missing']);
                        elseif (!$required && !$item) continue;
                        $fields = collect($item->fields);
                        $field = $fields->where('id', $item_field->id)->first();
                        array_push($field_results, $field);
                    }
                }
                $updater->setBid($bid)->setStatus($request->status)->setFieldResults($field_results)->setProposal($request->proposal)->setPrice($request->price)->update();
                return api_response($request, null, 200, ['bid' => $bid->id]);
            } else {
                /** @var Procurement $procurement */
                $procurement = $procurement_repository->find($request->procurement_id);
                $procurement->load('items.fields');
                $items = collect(json_decode($request->items));
                $field_results = [];
                foreach ($procurement->items as $procurement_item) {
                    $item = $items->where('id', $procurement_item->id)->first();
                    foreach ($procurement_item->fields as $item_field) {
                        $variables = json_decode($item_field->variables);
                        $required = (int)$variables->is_required;
                        if ($required && !$item) return api_response($request, null, 400, ['message' => $procurement_item->type . ' missing']);
                        elseif (!$required && !$item) continue;
                        $fields = collect($item->fields);
                        $field = $fields->where('id', $item_field->id)->first();
                        array_push($field_results, $field);
                    }
                }
                $bid = $creator->setBidder($request->partner)->setProcurement($procurement)->setStatus($request->status)
                    ->setProposal($request->proposal)
                    ->setFieldResults($field_results)
                    ->setPrice($request->price)->create();
                return api_response($request, null, 200, ['bid' => $bid->id]);
            }
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

    public function takeAction($partner, $bid, Request $request, BidRepositoryInterface $bid_repository, Updater $updater)
    {
        $this->validate($request, ['status' => 'required|string|in:accepted,rejected,sent,pending']);
        $bid = $bid_repository->find((int)$bid);
        $this->setModifier($request->manager_resource);
        $updater->setBid($bid)->setStatus($request->status)->updateStatus();
        return api_response($request, $bid, 200);
    }
}
