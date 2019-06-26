<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Throwable;

class PurchaseRequestController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $business = $request->business;

            list($offset, $limit) = calculatePagination($request);
            $purchase_requests_base_query = PurchaseRequest::with('member.profile')
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC');

            if ($request->has('status')) {
                $purchase_requests_base_query = $purchase_requests_base_query->where('status', $request->status);
            }

            if ($request->has('date_range')) {

            }

            $purchase_requests = $purchase_requests_base_query->skip($offset)->limit($limit)->get();
            $purchase_request_lists = collect();
            foreach ($purchase_requests as $purchase_request) {
                $purchase_request_lists->push([
                    'id' => $purchase_request->id,
                    'employee_name' => $purchase_request->member->profile->name,
                    'employee_image' => $purchase_request->member->profile->pro_pic,
                    'title' => $purchase_request->title,
                    'est_price' => $purchase_request->estimated_price,
                    'required_date' => $purchase_request->estimated_date ? $purchase_request->estimated_date->format('d/m/Y') : 'N/A',
                    'status' => $purchase_request->status
                ]);
            }

            if (count($purchase_request_lists) > 0) {
                return api_response($request, $purchase_request_lists, 200, ['data' => $purchase_request_lists]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forms(Request $request)
    {
        try {
            $business = $request->business;
            $purchase_request_forms = FormTemplate::for(config('b2b.FORM_TEMPLATES.purchase_request'))
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}