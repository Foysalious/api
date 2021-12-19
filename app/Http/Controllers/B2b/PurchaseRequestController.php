<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\PosOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestApproval;
use App\Sheba\Business\ACL\AccessControl;
use App\Transformers\CustomSerializer;
use App\Transformers\OfferDetailsTransformer;
use App\Transformers\OfferTransformer;
use App\Transformers\PosOrderTransformer;
use App\Transformers\PosServiceTransformer;
use App\Transformers\PurchaseRequestTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\Purchase\Creator;
use Sheba\Business\Purchase\StatusChanger;
use Sheba\ModificationFields;
use Throwable;
use Validator;

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
            $rules = [
                'from' => 'date_format:Y-m-d',
                'to' => 'date_format:Y-m-d|required_with:from'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $error = $validator->errors()->all()[0];
                return api_response($request, $error, 400, ['msg' => $error]);
            }

            $business = $request->business;

            list($offset, $limit) = calculatePagination($request);
            $purchase_requests_base_query = PurchaseRequest::with('member.profile')
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC');

            $purchase_requests_base_query = $this->listFiltering($request, $purchase_requests_base_query);
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
     * @param AccessControl $access_control
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string:in:product,service',
                'form_template_id' => 'sometimes|numeric',
                'title' => 'required|string',
                'items' => 'required|string',
                'questions' => 'sometimes|string',
                'estimated_price' => 'sometimes|numeric',
                'estimated_date' => 'sometimes|date_format:Y-m-d'
            ]);
            $this->setModifier($request->manager_member);

            $creator->setType($request->type)
                ->setTitle($request->title)
                ->setEstimatedPrice($request->estimated_price)
                ->setEstimatedDate($request->estimated_date)
                ->setBusiness($request->business)
                ->setMember($request->manager_member)
                ->setFormTemplateId($request->form_template_id)
                ->setLongDescription($request->description)
                ->setItems($request->items)
                ->setQuestions($request->questions);

            $procurement = $creator->create();
            return api_response($request, null, 200, ['id' => $procurement->id]);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        try {
            /** @var PurchaseRequest $purchase_request */
            $purchase_request = PurchaseRequest::with('items', 'approvalRequests')->find($request->purchase_request);
            if (!$purchase_request) return api_response($request, null, 404, ['msg' => 'Request Not Found']);

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($purchase_request, new PurchaseRequestTransformer());
            $purchase_request = $manager->createData($resource)->toArray()['data'];

            return api_response($request, null, 200, ['purchase_request' => $purchase_request]);
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

    /**
     * @param Request $request
     * @param StatusChanger $status_changer
     * @return JsonResponse
     */
    public function changeStatus(Request $request, StatusChanger $status_changer)
    {
        try {
            $this->validate($request, ['status' => 'required|string']);
            $this->setModifier($request->manager_member);

            $purchase_request = PurchaseRequest::find($request->purchase_request);
            $status_changer->setPurchaseRequest($purchase_request)->setData($request->all());

            if ($error = $status_changer->hasError())
                return api_response($request, $error, 400, ['message' => $error]);

            $status_changer->change();

            return api_response($request, null, 200);
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

    public function memberApprovalRequest(Request $request, StatusChanger $changer)
    {
        try {
            $this->validate($request, [
                'members' => 'required|string'
            ]);
            $this->setModifier($request->manager_member);
            $purchase_request = PurchaseRequest::find($request->purchase_request);

            $changer->setPurchaseRequest($purchase_request)->setData(['status' => config('b2b.PURCHASE_REQUEST_STATUS.need_approval')]);
            if ($error = $changer->hasError())
                return api_response($request, $error, 400, ['message' => $error]);
            $changer->change();

            $members = explode(',', $request->members);
            foreach ($members as $member) {
                PurchaseRequestApproval::create($this->withCreateModificationField([
                    'member_id' => $member,
                    'purchase_request_id' => $request->purchase_request
                ]));
            }
            return api_response($request, null, 200, ['msg' => 'Request Created Successfully']);
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

    /**
     * @param Request $request
     * @param $purchase_requests_base_query
     * @return mixed
     */
    private function listFiltering(Request $request, $purchase_requests_base_query)
    {
        if ($request->filled('status')) {
            $purchase_requests_base_query = $purchase_requests_base_query->where('status', $request->status);
        }

        if (($request->filled('from') && $request->from !== "null")) {
            $purchase_requests_base_query = $purchase_requests_base_query->whereBetween('created_at', [$request->from . " 00:00:00", $request->to . " 23:59:59"]);
        }

        if (($request->filled('q') && $request->q !== "null")) {
            $purchase_requests_base_query = $purchase_requests_base_query->where('title', 'LIKE', '%' . $request->q . '%');
        }

        return $purchase_requests_base_query;
    }
}