<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class PurchaseRequestController extends Controller
{
    use ModificationFields;

    public function forms(Request $request)
    {
        try {
            $business = $request->business;
            $purchase_request_forms = FormTemplate::for('purchase_request')
                ->businessOwner($business->id)
                ->get();

            $form_lists = collect();
            foreach ($purchase_request_forms as $purchase_request_form) {
                $form_lists->push([
                    'id' => $purchase_request_form->id,
                    'title' => $purchase_request_form->title,
                    'short_description' => $purchase_request_form->short_description
                ]);
            }

            if (count($form_lists) > 0) return api_response($request, $form_lists, 200, ['data' => $form_lists->unique()->values()]);
            else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}