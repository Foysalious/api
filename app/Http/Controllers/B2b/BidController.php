<?php namespace App\Http\Controllers\B2b;

use App\Models\Procurement;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use App\Sheba\Business\ACL\AccessControl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BidController extends Controller
{
    use ModificationFields;

    public function index($business, $procurement, Request $request, AccessControl $access_control)
    {
        try {
            $access_control->setBusinessMember($request->business_member);
            if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);
            $business = $request->business;
            $procurement = Procurement::findOrFail((int)$procurement);

            $bids = $procurement->bids();
            $bid_lists = [];
            foreach ($bids->get() as $bid) {
                $model = $bid->bidder_type;
                $bidder = $model::findOrFail((int)$bid->bidder_id);
                /* $reviews = $bidder->reviews;*/
                /*dd($reviews->first());*/
                $bid_items = $bid->bidItems;
                $item_type = [];
                foreach ($bid_items as $item) {
                    $item_fields = [];
                    $fields = $item->fields;
                    foreach ($fields as $field) {
                        array_push($item_fields, [
                            'question' => $field->title,
                            'answer' => $field->result
                        ]);
                    }
                    array_push($item_type, [
                        'item_type' => $item->type,
                        'fields' => $item_fields
                    ]);
                }
                array_push($bid_lists, [
                    'name' => $bidder->name,
                    'logo' => $bidder->logo,
                    'item' => $item_type,
                ]);
            }
            if (count($bid_lists) > 0) return api_response($request, $bid_lists, 200, ['bid_lists' => $bid_lists]);
            else return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}