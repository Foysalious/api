<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Transformers\CustomSerializer;
use App\Transformers\PosOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Pos\Customer\Creator;
use Sheba\Pos\Customer\Updater;
use Sheba\Pos\Repositories\PosOrderRepository;
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
            $partner           = $request->partner;
            $partner_customers = PartnerPosCustomer::byPartner($partner->id)->get();
            $customers         = collect();
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
     * @param EntryRepository $entry_repo
     * @return JsonResponse
     */
    public function show($partner, $customer, Request $request, EntryRepository $entry_repo)
    {
        try {
            /** @var PosCustomer $customer */
            $customer = PosCustomer::find((int)$customer);
            if (!$customer)
                return api_response($request, null, 404, ['message' => 'Customer Not Found.']);
            $data                             = $customer->details();
            $data['customer_since']           = $customer->created_at->format('Y-m-d');
            $data['customer_since_formatted'] = $customer->created_at->diffForHumans();
            $total_purchase_amount            = 0.00;
            $total_due_amount                 = 0.00;
            PosOrder::byPartner($partner)->byCustomer($customer->id)->get()->each(function ($order) use (&$total_purchase_amount, &$total_due_amount) {
                /** @var PosOrder $order */
                $order                 = $order->calculate();
                $total_purchase_amount += $order->getNetBill();
                $total_due_amount      += $order->getDue();
            });
            $data['total_purchase_amount'] = $total_purchase_amount;
            $data['total_due_amount']      = $total_due_amount;
            $data['total_used_promo']      = 0.00;
            $data['total_payable_amount']  = $entry_repo->setPartner($request->partner)->getTotalPayableAmountByCustomer($customer->profile_id)['total_payables'];
            $data['is_customer_editable']  = $customer->isEditable();
            $data['note']                  = PartnerPosCustomer::where('customer_id', $customer->id)->where('partner_id', $partner)->first()->note;
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
            $this->validate($request, [
                'mobile' => 'required|mobile:bd',
                'name'   => 'required'
            ]);
            $this->setModifier($request->manager_resource);
            $creator = $creator->setData($request->except([
                'partner_id',
                'remember_token'
            ]));
            if ($error = $creator->hasError())
                return api_response($request, null, 400, ['message' => $error['msg']]);
            $customer = $creator->setPartner($request->partner)->create();
            return api_response($request, $customer, 200, ['customer' => $customer->details()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
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
            $updater->setCustomer($customer)->setData($request->except([
                'partner_id',
                'remember_token'
            ]));
            if ($error = $updater->hasError())
                return api_response($request, null, 400, ['message' => $error['msg']]);
            $customer = $updater->update();
            return api_response($request, $customer, 200, ['customer' => $customer->details()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @param PosCustomer $customer
     * @return JsonResponse
     */
    public function orders(Request $request, $partner, PosCustomer $customer)
    {
        ini_set('memory_limit', '2096M');
        try {
            $partner = $request->partner;
            $status  = $request->status;
            list($offset, $limit) = calculatePagination($request);
            /** @var PosOrder $orders */
            $orders       = PosOrder::with('items.service.discounts', 'customer', 'payments', 'logs', 'partner')->byPartner($partner->id)->byCustomer($customer->id)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
            $final_orders = [];
            foreach ($orders as $index => $order) {
                $order->isRefundable();
                $order_data = $order->calculate();
                $manager    = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource          = new Item($order_data, new PosOrderTransformer());
                $order_formatted   = $manager->createData($resource)->toArray()['data'];
                $order_create_date = $order->created_at->format('Y-m-d');
                if (!isset($final_orders[$order_create_date]))
                    $final_orders[$order_create_date] = [];
                if (($status == "null") || !$status || ($status && $order->getPaymentStatus() == $status)) {
                    array_push($final_orders[$order_create_date], $order_formatted);
                }
            }
            $orders_formatted = [];
            $pos_orders_repo  = new PosOrderRepository();
            $pos_sales        = [];
            foreach (array_keys($final_orders) as $date) {
                $timeFrame = new TimeFrame();
                $timeFrame->forADay(Carbon::parse($date))->getArray();
                $pos_orders = $pos_orders_repo->getCreatedOrdersBetweenByPartnerAndCustomer($timeFrame, $partner, $customer);
                $pos_orders->map(function ($pos_order) {
                    /** @var PosOrder $pos_order */
                    $pos_order->sale = $pos_order->getNetBill();
                    $pos_order->due  = $pos_order->getDue();
                });
                $pos_sales[$date] = [
                    'total_sale' => $pos_orders->sum('sale'),
                    'total_due'  => $pos_orders->sum('due')
                ];
            }
            foreach ($final_orders as $key => $value) {
                if (count($value) > 0) {
                    $order_list = [
                        'date'       => $key,
                        'total_sale' => $pos_sales[$key]['total_sale'],
                        'total_due'  => $pos_sales[$key]['total_due'],
                        'orders'     => $value
                    ];
                    array_push($orders_formatted, $order_list);
                }
            }
            return api_response($request, $orders_formatted, 200, ['orders' => $orders_formatted]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function delete(Request $request, $partner, $customer, DueTrackerRepository $dueTrackerRepository)
    {
        try {
            $partner_pos_customer = PartnerPosCustomer::byPartner($request->partner->id)->where('customer_id', $customer)->with(['customer'])->first();
            /** @var PosCustomer $customer */
            if (empty($partner_pos_customer) || empty($partner_pos_customer->customer))
                throw new InvalidPartnerPosCustomer();
            $customer = $partner_pos_customer->customer;
            $dueTrackerRepository->setPartner($request->partner)->removeCustomer($customer->profile_id);
            $customer->delete();
            return api_response($request, true, 200);
        } catch (InvalidPartnerPosCustomer $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
