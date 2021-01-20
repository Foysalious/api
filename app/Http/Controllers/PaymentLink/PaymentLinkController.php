<?php namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Transformers\PaymentDetailTransformer;
use App\Transformers\PaymentLinkArrayTransform;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Exception;
use Sheba\EMI\Calculations;
use Sheba\ModificationFields;
use Sheba\PaymentLink\Creator;
use Sheba\PaymentLink\PaymentLink;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use Sheba\Usage\Usage;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentLinkController extends Controller
{
    use ModificationFields;

    private $paymentLinkClient;
    private $paymentLinkRepo;
    private $creator;
    private $paymentDetailTransformer;

    public function __construct(PaymentLinkClient $payment_link_client, PaymentLinkRepository $payment_link_repo, Creator $creator)
    {
        $this->paymentLinkClient        = $payment_link_client;
        $this->paymentLinkRepo          = $payment_link_repo;
        $this->creator                  = $creator;
        $this->paymentDetailTransformer = new PaymentDetailTransformer();
    }

    public function getDashboard(Request $request, PaymentLink $link)
    {
        try {
            $default_payment_link = $this->paymentLinkClient->defaultPaymentLink($request);
            if ($default_payment_link) {
                $default_payment_link = $link->defaultPaymentLinkData($default_payment_link);
            } else {
                $request->merge(['isDefault' => 1]);
                $this->creator->setIsDefault($request->isDefault)->setAmount($request->amount)->setReason($request->purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type);
                $store_default_link   = $this->creator->save();
                $default_payment_link = $link->defaultPaymentLinkData($store_default_link, 0);
            }
            $dashboard = $link->dashboard();
            return api_response($request, $dashboard, 200, ["data" => $dashboard]);
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
                $payment_links_list = array_where($payment_links_list, function ($key, $link) {
                    return array_key_exists('targetType', $link) ? $link['targetType'] == null : $link;
                });
                list($offset, $limit) = calculatePagination($request);
                $links         = collect($payment_links_list)->slice($offset)->take($limit);
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

    public function show($identifier, Request $request, PaymentLinkRepositoryInterface $paymentLinkRepository)
    {
        try {
            $link = $paymentLinkRepository->findByIdentifier($identifier);
            if($link && !(int)$link->getIsActive()) {
                return api_response($request,$link,203,['info'=>$link->partialInfo()]);
            }
            if ($link && (int)$link->getIsActive()) {
               return api_response($request,$link,200,['link'=>$link->toArray()]);
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
                'amount'    => 'required',
                'purpose'   => 'required', 'customer_id' => 'sometimes|integer|exists:pos_customers,id',
                'emi_month' => 'sometimes|integer|in:' . implode(',', config('emi.valid_months'))
            ]);
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
                ->setEmiCalculations();

            if($request->has('pos_order_id')){
                $pos_order = PosOrder::find($request->pos_order_id);
                $customer = PosCustomer::find($pos_order->customer_id);
                if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            }

            if ($request->has('customer_id')) {
                $customer = PosCustomer::find($request->customer_id);
                if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            }

            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
                if(!$request->has('emi_month')) {
                    $this->creator->sentSms();
                }
                $message = "অভিনন্দন! আপনি সফলভাবে একটি কাস্টম লিঙ্ক তৈরি করেছেন। লিঙ্কটি শেয়ার করার মাধ্যমে টাকা গ্রহণ করুন।";
                $title = "লিঙ্ক তৈরি সফল হয়েছে";
                return api_response($request, $payment_link, 200,
                    ['payment_link' => $payment_link,"message" => $message,"title" => $title]);
            } else {
                $message = "দুঃখিত! কিছু একটা সমস্যা হয়েছে, লিঙ্ক তৈরি করা সম্ভব হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।";
                $title = "লিঙ্ক তৈরি হয়নি";
                return api_response($request, null, 500,["message" => $message,"title" => $title]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $message = "দুঃখিত! কিছু একটা সমস্যা হয়েছে, লিঙ্ক তৈরি করা সম্ভব হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।";
            $title = "লিঙ্ক তৈরি হয়নি";
            return api_response($request, null, 500,["message" => $message,"title" => $title]);
        }
    }

    public function createPaymentLinkForDueCollection(Request $request)
    {
        try {
            $this->validate($request, [
                'amount'      => 'required|numeric',
                'customer_id' => 'sometimes|integer|exists:pos_customers,id',
                'emi_month'   => 'sometimes|integer|in:' . implode(',', config('emi.valid_months'))
            ]);
            $purpose = 'Due Collection';
            if ($request->has('customer_id')) $customer = PosCustomer::find($request->customer_id);

            $this->creator->setAmount($request->amount)->setReason($purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type);
            if (isset($customer) && !empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            if ($request->emi_month) {
                $data = Calculations::getMonthData($request->amount, (int)$request->emi_month, false);
                $this->creator->setAmount($data['total_amount'])->setInterest($data['total_interest'])->setBankTransactionCharge($data['bank_transaction_fee'])->setEmiMonth((int)$request->emi_month);
            }
            $this->creator->setTargetType('due_tracker')->setTargetId(1);
            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
                if(!$request->has('emi_month')) {
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
            app('sentry')->captureException($e);
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
                return api_response($request, 1, 200, $this->creator->getSuccessMessage($request));
            } else {
                return api_response($request, null, 500, $this->creator->getErrorMessage($request));
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, $this->creator->getErrorMessage($request));
        }
    }

    public function getDefaultLink(Request $request)
    {
        try {
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
                $payables    = $this->paymentLinkRepo->payables($payment_link_details);
                $all_payment = [];
                foreach ($payables->get() as $payable) {
                    $payments = $payable->payments ? $payable->payments : null;
                    foreach ($payments as $payment) {
                        $payment = [
                            'id'         => $payment ? $payment->id : null,
                            'code'       => $payment ? '#' . $payment->id : null,
                            'name'       => $payment ? $payment->payable->getName() : null,
                            'amount'     => $payment ? $payable->amount : null,
                            'created_at' => $payment ? Carbon::parse($payment->created_at)->format('Y-m-d h:i a') : null,
                        ];
                        array_push($all_payment, $payment);
                    }
                }
                $payment_link_payments = [
                    'id'             => $payment_link_details['linkId'],
                    'code'           => '#' . $payment_link_details['linkId'],
                    'purpose'        => $payment_link_details['reason'],
                    'status'         => $payment_link_details['isActive'] == 1 ? 'active' : 'inactive',
                    'payment_link'   => $payment_link_details['link'],
                    'amount'         => $payment_link_details['amount'],
                    'total_payments' => $payables->count(),
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
            $payment                      = $this->paymentLinkRepo->payment($payment);
            if ($payment_link_payment_details) {
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
            $payment_links_list = $this->paymentLinkRepo->getPaymentLinkList($request);
            if (is_array($payment_links_list) && count($payment_links_list) > 0) {
                $transactionList = [];
                foreach ($payment_links_list as $link) {
                    $transactions = DB::table('payments as pa')
                        ->join('payables as pb', 'pa.payable_id', '=', 'pb.id')
                        ->where('type', 'payment_link')
                        ->where('type_id', $link['linkId'])
                        ->get()
                    ;

                    foreach ($transactions as $transaction) {
                        array_push($transactionList, $transaction);
                    }
                }

                return api_response($request, null, 200, ['transactions' => $transactionList]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
