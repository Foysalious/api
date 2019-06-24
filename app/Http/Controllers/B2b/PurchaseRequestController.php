<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class PurchaseRequestController extends Controller
{
    use ModificationFields;

    public function forms(Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $purchase_requests = PurchaseRequest::with('formTemplate')
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC')
                ->get();

            $form_lists = collect();
            foreach ($purchase_requests as $purchase_request) {
                $purchase_request_form = $purchase_request->formTemplate;
                $form_lists->push([
                    'id' => $purchase_request_form ? $purchase_request_form->id : null,
                    'title' => $purchase_request_form ? $purchase_request_form->title : null
                ]);
            }

            if (count($form_lists) > 0)
                return api_response($request, $form_lists, 200, ['form_lists' => $form_lists->unique()->values()]); else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}