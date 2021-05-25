<?php namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Transformers\PaymentDetailTransformer;
use App\Transformers\PaymentLinkArrayTransform;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\ModificationFields;
use Sheba\PaymentLink\Creator;
use Sheba\PaymentLink\PaymentLink;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkController extends Controller
{
    use ModificationFields;

    private $paymentLinkClient;
    private $paymentLinkRepo;
    private $creator;
    private $paymentDetailTransformer;
    private $paymentLinkTransactionDetailTransformer;

    public function __construct(PaymentLinkClient $payment_link_client, PaymentLinkRepository $payment_link_repo, Creator $creator)
    {
        $this->paymentLinkClient        = $payment_link_client;
        $this->paymentLinkRepo          = $payment_link_repo;
        $this->creator                  = $creator;
        $this->paymentDetailTransformer = new PaymentDetailTransformer();

    }

    /**
     * @param Request     $request
     * @param PaymentLink $link
     * @return JsonResponse
     */
    public function getDashboard(Request $request, PaymentLink $link)
    {
        try {
            if (!$request->user) return api_response($request, null, 404, ['message' => 'User not found']);
            $default_payment_link = $this->paymentLinkClient->defaultPaymentLink($request);
            if ($default_payment_link) {
                $link->defaultPaymentLinkData($default_payment_link);
            } else {
                $request->merge(['isDefault' => 1]);
                $this->creator->setIsDefault($request->isDefault)->setAmount($request->amount)->setReason($request->purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type);
                $store_default_link = $this->creator->save();
                $link->defaultPaymentLinkData($store_default_link, 0);
            }
            $dashboard = $link->dashboard();
            return api_response($request, $dashboard, 200, ["data" => $dashboard]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customLinkCreateData(Request $request)
    {
        try {
            $data = PaymentLinkStatics::customPaymentLinkData();
            return api_response($request, $data, 200, ["data" => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $payment_links_list = $this->paymentLinkRepo->getPaymentLinkList($request);
            if ($payment_links_list) {
//                $payment_links_list = array_where($payment_links_list, function ($key, $link) {
//                    return array_key_exists('targetType', $link) ? $link['targetType'] == null : $link;
//                });
//                list($offset, $limit) = calculatePagination($request);
                $links         = collect($payment_links_list);
                $fractal       = new Manager();
                $resources     = new Collection($links, new PaymentLinkArrayTransform());
                $payment_links = $fractal->createData($resources)->toArray()['data'];
                return api_response($request, $payment_links, 200, ['payment_links' => $payment_links]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function partnerPaymentLinks(Request $request)
    {
        try {
            $payment_links_list = $this->paymentLinkRepo->getPartnerPaymentLinkList($request);
            if ($payment_links_list) {
                $links         = collect($payment_links_list);
                $fractal       = new Manager();
                $resources     = new Collection($links, new PaymentLinkArrayTransform());
                $payment_links = $fractal->createData($resources)->toArray()['data'];
                return api_response($request, $payment_links, 200, ['payment_links' => $payment_links]);
            } else {
                return api_response($request, [], 200, ['payment_links' => []]);
            }
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function show($identifier, Request $request, PaymentLinkRepositoryInterface $paymentLinkRepository)
    {
        try {
            $link = $paymentLinkRepository->findByIdentifier($identifier);
            if ($link && !(int)$link->getIsActive()) {
                return api_response($request, $link, 203, ['info' => $link->partialInfo()]);
            }
            if ($link && (int)$link->getIsActive()) {
                return api_response($request, $link, 200, ['link' => $link->toArray()]);
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'amount'             => 'required',
                'purpose'            => 'required', 'customer_id' => 'sometimes|integer|exists:pos_customers,id',
                'emi_month'          => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
                'interest_paid_by'   => 'sometimes|in:' . implode(',', PaymentLinkStatics::paidByTypes()),
                'transaction_charge' => 'sometimes|numeric|min:' . PaymentLinkStatics::get_payment_link_commission()
            ]);
            if (!$request->user) return api_response($request, null, 404, ['message' => 'User not found']);
            $emi_month_invalidity = Creator::validateEmiMonth($request->all());
            if ($emi_month_invalidity !== false) return api_response($request, null, 400, ['message' => $emi_month_invalidity]);
            $this->creator
                ->setIsDefault($request->isDefault)
                ->setAmount($request->amount)
                ->setReason($request->purpose)
                ->setUserName($request->user->name)
                ->setUserId($request->user->id)
                ->setUserType($request->type)
                ->setTargetId($request->pos_order_id)
                ->setTargetType('pos_order')
                ->setEmiMonth((int)$request->emi_month)
                ->setPaidBy($request->interest_paid_by ?: PaymentLinkStatics::paidByTypes()[($request->has("emi_month") ? 1 : 0)])
                ->setTransactionFeePercentage($request->transaction_charge)
                ->calculate();

            if ($request->has('pos_order_id')) {
                $pos_order = PosOrder::find($request->pos_order_id);
                $this->deActivatePreviousLink($pos_order);
                $customer = PosCustomer::find($pos_order->customer_id);
                if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
                if ($this->creator->getPaidBy() == PaymentLinkStatics::paidByTypes()[1]) {
                    $pos_order->update(['interest' => $this->creator->getInterest(), 'bank_transaction_charge' => $this->creator->getBankTransactionCharge()]);
                } else {
                    $pos_order->update(['interest' => 0, 'bank_transaction_charge' => 0]);
                }

            }

            if ($request->has('customer_id')) {
                $customer = PosCustomer::find($request->customer_id);
                if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            }

            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
                if (!$request->has('emi_month')) {
                    $this->creator->sentSms();
                }
                return api_response($request, $payment_link, 200, array_merge(['payment_link' => $payment_link], $this->creator->getSuccessMessage()));
            } else {
                return api_response($request, null, 500, $this->creator->getErrorMessage());
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, $this->creator->getErrorMessage());
        }
    }

    private function deActivatePreviousLink(PosOrder $order)
    {
        $payment_link_target = $order->getPaymentLinkTarget();
        $payment_link        = app(PaymentLinkRepositoryInterface::class)->getPaymentLinksByPosOrder($payment_link_target);
        $key                 = $payment_link_target->toString();
        $links               = null;
        if (array_key_exists($key, $payment_link))
            $links = $payment_link[$key];
        if ($links) {
            foreach ($links as $link) {
                $this->creator->setStatus('deactivate')->setPaymentLinkId($link->getLinkID())->editStatus();
            }
        }
    }

    public function createPaymentLinkForDueCollection(Request $request)
    {
        try {
            $this->validate($request, [
                'amount'             => 'required|numeric',
                'customer_id'        => 'sometimes|integer|exists:pos_customers,id',
                'emi_month'          => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
                'interest_paid_by'   => 'sometimes|in:' . implode(',', PaymentLinkStatics::paidByTypes()),
                'transaction_charge' => 'sometimes|numeric|min:' . PaymentLinkStatics::get_payment_link_commission()
            ]);
            $purpose = 'Due Collection';
            if (!$request->user) return api_response($request, null, 404, ['message' => 'User not found']);
            if ($request->has('customer_id')) $customer = PosCustomer::find($request->customer_id);

            $this->creator->setAmount($request->amount)
                          ->setReason($purpose)
                          ->setUserName($request->user->name)
                          ->setUserId($request->user->id)
                          ->setUserType($request->type)
                          ->setEmiMonth($request->emi_month ?: 0)
                          ->setPaidBy($request->interest_paid_by ?: PaymentLinkStatics::paidByTypes()[($request->has("emi_month") ? 1 : 0)])
                          ->setTransactionFeePercentage($request->transaction_charge);
            if (isset($customer) && !empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            $this->creator->setTargetType('due_tracker')->setTargetId(1)->calculate();
            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
                if (!$request->has('emi_month')) {
                    $this->creator->sentSms();
                }
                return api_response($request, $payment_link, 200, ['payment_link' => $payment_link]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function statusChange($link, Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'required'
            ]);
            $this->creator->setStatus($request->status)->setPaymentLinkId($link);
            $payment_link_status_change = $this->creator->editStatus();
            if ($payment_link_status_change) {
                return api_response($request, 1, 200, $this->creator->getSuccessMessage($request->status));
            } else {
                return api_response($request, null, 500, $this->creator->getErrorMessage($request->status));
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message, 'title' => "validation fail"]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, $this->creator->getErrorMessage($request->status));
        }
    }

    public function getDefaultLink(Request $request)
    {
        try {
            if (!$request->user) return api_response($request, null, 404, ['message' => 'User not found']);
            $default_payment_link = $this->paymentLinkClient->defaultPaymentLink($request);
            if ($default_payment_link) {
                $default_payment_link = [
                    'link_id' => $default_payment_link[0]['linkId'],
                    'link'    => $default_payment_link[0]['link'],
                    'amount'  => $default_payment_link[0]['amount'],
                ];
                return api_response($request, $default_payment_link, 200, ['default_payment_link' => $default_payment_link]);
            } else {
                $request->merge(['isDefault' => 1]);
                if (empty($request->user->name)) $request->user->name = "UnknownName";
                $this->creator->setIsDefault($request->isDefault)->setAmount($request->amount)->setReason($request->purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type);
                $store_default_link   = $this->creator->save();
                $default_payment_link = [
                    'link_id' => $store_default_link->linkId,
                    'link'    => $store_default_link->link,
                    'amount'  => $store_default_link->amount,
                ];
                return api_response($request, $default_payment_link, 200, ['default_payment_link' => $default_payment_link]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentLinkPayments($link, Request $request)
    {
        try {
            $payment_link_details = $this->paymentLinkClient->paymentLinkDetails($link);
            if ($payment_link_details) {
                $payments    = $this->paymentLinkRepo->payables($payment_link_details);
                $all_payment = [];
                foreach ($payments->get() as $payment) {
                    $payment = [
                        'id'         => $payment->id,
                        'code'       => '#' . $payment->id,
                        'name'       => $payment->payable->getName(),
                        'amount'     => $payment->amount,
                        'created_at' => Carbon::parse($payment->created_at)->format('Y-m-d h:i a'),
                    ];
                    array_push($all_payment, $payment);
                }
                $payment_link_payments = [
                    'id'             => $payment_link_details['linkId'],
                    'code'           => '#' . $payment_link_details['linkId'],
                    'purpose'        => $payment_link_details['reason'],
                    'status'         => $payment_link_details['isActive'] == 1 ? 'active' : 'inactive',
                    'payment_link'   => $payment_link_details['link'],
                    'amount'         => $payment_link_details['amount'],
                    'total_payments' => $payments->count(),
                    'created_at'     => date('Y-m-d h:i a', $payment_link_details['createdAt'] / 1000),
                    'payments'       => $all_payment
                ];
                return api_response($request, $payment_link_payments, 200, ['payment_link_payments' => $payment_link_payments]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function paymentLinkPaymentDetails($link, $payment, Request $request)
    {
        try {
            $payment_link_payment_details = $this->paymentLinkRepo->paymentLinkDetails($link);
            $payment                      = $payment_link_payment_details ? $this->paymentLinkRepo->payment($payment) : null;
            if ($payment && $payment_link_payment_details) {
                $payment_detail  = $payment->paymentDetails ? $payment->paymentDetails->last() : null;
                $payment_details = $this->paymentDetailTransformer->transform($payment, $payment_detail, $payment_link_payment_details);
                return api_response($request, $payment_details, 200, ['payment_details' => $payment_details]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function transactionList(Request $request, Payable $payable)
    {
        try {
            $data = $this->paymentLinkRepo->getPaymentList($request);
            if ($data) {
                return api_response($request, null, 200, ['data' => $data]);
            } else {
                return api_response($request, null, 200, ['data' => []]);
            }
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
