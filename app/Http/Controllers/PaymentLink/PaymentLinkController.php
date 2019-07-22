<?php namespace App\Http\Controllers\PaymentLink;

use Illuminate\Validation\ValidationException;
use Sheba\PaymentLink\Creator;
use Sheba\PaymentLink\PaymentLinkClient;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Payable;
use App\Models\Payment;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkController extends Controller
{
    use ModificationFields;
    private $paymentLinkClient;
    private $paymentLinkRepo;
    private $creator;

    public function __construct(PaymentLinkClient $payment_link_client, PaymentLinkRepository $payment_link_repo, Creator $creator)
    {
        $this->paymentLinkClient = $payment_link_client;
        $this->paymentLinkRepo = $payment_link_repo;
        $this->creator = $creator;
    }

    public function index(Request $request)
    {
        try {
            $payment_links_list = $this->paymentLinkClient->paymentLinkList($request);
            $payment_links = [];
            if ($payment_links_list) {
                list($offset, $limit) = calculatePagination($request);
                $links = collect($payment_links_list)->slice($offset)->take($limit);
                foreach ($links as $link) {
                    $link = [
                        'id' => $link['linkId'],
                        'code' => '#' . $link['linkId'],
                        'purpose' => $link['reason'],
                        'status' => $link['isActive'] == 1 ? 'active' : 'inactive',
                        'amount' => $link['amount'],
                        'created_at' => date('Y-m-d h:i a', $link['createdAt'] / 1000),
                    ];
                    array_push($payment_links, $link);
                }
                return api_response($request, $payment_links, 200, ['payment_links' => $payment_links]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
                'purpose' => 'required',
            ]);
            $this->creator->setIsDefault($request->isDefault)
                ->setAmount($request->amount)
                ->setReason($request->purpose)
                ->setUserName($request->user->name)
                ->setUserId($request->user->id)
                ->setUserType($request->type)
                ->setTargetId($request->posOrderId)
                ->setTarget('pos_order');

            $payment_link_store = $this->creator->save();

            if ($payment_link_store) {
                $payment_link = [
                    'link_id' => $payment_link_store->linkId,
                    'reason' => $payment_link_store->reason,
                    'type' => $payment_link_store->type,
                    'status' => $payment_link_store->isActive == 1 ? 'active' : 'inactive',
                    'amount' => $payment_link_store->amount,
                    'link' => $payment_link_store->link,
                ];
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
                return api_response($request, 1, 200);
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

    public function getDefaultLink(Request $request)
    {
        try {
            $default_payment_link = $this->paymentLinkClient->defaultPaymentLink($request);
            if ($default_payment_link) {
                $default_payment_link = [
                    'link_id' => $default_payment_link[0]['linkId'],
                    'link' => $default_payment_link[0]['link'],
                    'amount' => $default_payment_link[0]['amount'],
                ];
                return api_response($request, $default_payment_link, 200, ['default_payment_link' => $default_payment_link]);
            } else {
                $request->merge(['isDefault' => 1]);
                $this->creator->setIsDefault($request->isDefault)
                    ->setAmount($request->amount)
                    ->setReason($request->purpose)
                    ->setUserName($request->user->name)
                    ->setUserId($request->user->id)
                    ->setUserType($request->type);

                $store_default_link = $this->creator->save();
                $default_payment_link = [
                    'link_id' => $store_default_link->linkId,
                    'link' => $store_default_link->link,
                    'amount' => $store_default_link->amount,
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
                $payables = $this->paymentLinkRepo->payables($payment_link_details);
                $all_payment = [];
                foreach ($payables->get() as $payable) {
                    $payment = $payable->payment ? $payable->payment : null;
                    $payment = [
                        'id' => $payment ? $payment->id : null,
                        'code' => $payment ? '#' . $payment->id : null,
                        'name' => $payment ? $payment->created_by_name : null,
                        'amount' => $payment ? $payable->amount : null,
                        'created_at' => $payment ? Carbon::parse($payment->created_at)->format('Y-m-d h:i a') : null,
                    ];
                    array_push($all_payment, $payment);
                }
                $payment_link_payments = [
                    'id' => $payment_link_details['linkId'],
                    'code' => '#' . $payment_link_details['linkId'],
                    'purpose' => $payment_link_details['reason'],
                    'status' => $payment_link_details['isActive'] == 1 ? 'active' : 'inactive',
                    'payment_link' => $payment_link_details['link'],
                    'amount' => $payment_link_details['amount'],
                    'total_payments' => $payables->count(),
                    'created_at' => date('Y-m-d h:i a', $payment_link_details['createdAt'] / 1000),
                    'payments' => $all_payment
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
            $payment = $this->paymentLinkRepo->payment($payment);

            $model = $payment->created_by_type;
            $user = $model::find($payment->created_by);
            if ($payment_link_payment_details) {
                $payment_detail = $payment->paymentDetails ? $payment->paymentDetails->last() : null;
                $payment_details = [
                    'customer_name' => $payment->created_by_name,
                    'customer_number' => $user->mobile,
                    'payment_type' => $payment_detail->readableMethod,
                    'id' => $payment->id,
                    'payment_code' => '#' . $payment->id,
                    'amount' => $payment->payable->amount,
                    'created_at' => Carbon::parse($payment->created_at)->format('Y-m-d h:i a'),
                    'link' => $payment_link_payment_details['link'],
                    'link_code' => '#' . $payment_link_payment_details['linkId'],
                    'purpose' => $payment_link_payment_details['reason'],
                    'status' => $payment_link_payment_details['isActive'] == 1 ? 'active' : 'inactive'
                ];
                return api_response($request, $payment_details, 200, ['payment_details' => $payment_details]);
            } else {
                return api_response($request, 1, 404);
            }

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
