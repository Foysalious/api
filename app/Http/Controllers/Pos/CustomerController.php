<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\ModificationFields;
use Sheba\Pos\Customer\Creator;

use Sheba\Pos\Customer\Updater;
use Throwable;

class CustomerController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $partner_customers = PartnerPosCustomer::byPartner($partner->id)->get();
            $customers = collect();
            foreach ($partner_customers as $partner_customer) {
                /** @var PartnerPosCustomer $partner_customer */
                $customers->push($partner_customer->details());
            }
            return api_response($request, $customers, 200, ['customers' => $customers]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $customer
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, $customer, Request $request)
    {
        try {
            /** @var PosCustomer $customer */
            $customer = PosCustomer::find((int)$customer);
            if (!$customer) return api_response($request, null, 404, ['message' => 'Customer Not Found.']);

            $data = $customer->details();
            $data['customer_since'] = $customer->created_at->format('Y-m-d');
            $data['customer_since_formatted'] = $customer->created_at->diffForHumans();

            $total_purchase_amount = 0.00;
            $total_due_amount = 0.00;
            PosOrder::where('customer_id', $customer->id)->get()->each(function ($order) use (&$total_purchase_amount, &$total_due_amount) {
                /** @var PosOrder $order */
                $order = $order->calculate();
                $total_purchase_amount += $order->getTotalBill();
                $total_due_amount += $order->getDue();
            });

            $data['total_purchase_amount'] = $total_purchase_amount;
            $data['total_due_amount'] = $total_due_amount;
            $data['total_used_promo'] = 0.00;
            $data['is_customer_editable'] = $customer->isEditable();
            $data['note'] = PartnerPosCustomer::where('customer_id', $customer->id)->where('partner_id', $partner)->first()->note;

            return api_response($request, $customer, 200, ['customer' => $data]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, ['mobile' => 'required|mobile:bd', 'name' => 'required']);
            $this->setModifier($request->manager_resource);
            $creator = $creator->setData($request->except(['partner_id','remember_token']));
            if ($error = $creator->hasError())
                return api_response($request, null, 400, ['message' => $error['msg']]);

            $customer = $creator->create();
            return api_response($request, $customer, 200, ['customer' => $customer->details()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @param PosCustomer $customer
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update(Request $request, $partner, PosCustomer $customer, Updater $updater)
    {
        try {
            $this->validate($request, ['mobile' => 'required|mobile:bd']);
            $this->setModifier($request->manager_resource);
            $updater->setCustomer($customer)->setData($request->except(['partner_id','remember_token']));
            if ($error = $updater->hasError())
                return api_response($request, null, 400, ['message' => $error['msg']]);

            $customer = $updater->update();
            return api_response($request, $customer, 200, ['customer' => $customer->details()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
