<?php namespace App\Http\Controllers\PaymentLink;

use App\Models\Payable;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;
use Sheba\PaymentLink\PaymentLinkClient;

class PaymentLinkController extends Controller
{
    use ModificationFields;
    private $paymentLinkClient;

    public function __construct(PaymentLinkClient $payment_link_client)
    {
        $this->paymentLinkClient = $payment_link_client;
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

            $data = [
                'amount' => $request->amount,
                'reason' => $request->purpose,
                'userId' => $request->user->id,
                'userName' => $request->user->name,
                'userType' => $request->type
            ];
            $url = config('sheba.payment_link_url') . '/api/v1/payment-links';
            $client = new Client();
            $result = $client->request('POST', $url, ['form_params' => $data]);
            $result = json_decode($result->getBody());
            if ($result->code == 200) {
                $payment_link = [
                    'reason' => $result->link->reason,
                    'type' => $result->link->type,
                    'status' => $result->link->isActive == 1 ? 'active' : 'inactive',
                    'amount' => $result->link->amount,
                    'link' => $result->link->link,
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
            if ($request->status == 'active') {
                $status = 1;
            } else {
                $status = 0;
            }

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $url = "$url?isActive=$status";
            $client = new Client();
            $result = $client->request('PUT', $url, []);
            $result = json_decode($result->getBody());
            if ($result->code == 200) {
                return api_response($request, 1, 200);
            } elseif ($result->code == 404) {
                return api_response($request, 1, 404);
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
            if (1) {
                $default_payment_link = 'https:sheba.xyz@Venus';
                return api_response($request, $default_payment_link, 200, ['default_payment_link' => $default_payment_link]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentLinkPayments($link, Request $request)
    {
        try {
            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200) {

                $link = $response['link'];
                $payables = Payable::where([
                    ['type', 'payment_link'],
                    ['type_id', $link['linkId']],
                ])->select('id', 'type', 'type_id', 'amount')
                    ->with([
                        'payment' => function ($query) {
                            $query->where('status', 'completed')
                                ->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at');
                        }]);

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
                    'id' => $link['linkId'],
                    'code' => '#' . $link['linkId'],
                    'purpose' => $link['reason'],
                    'status' => $link['isActive'] == 1 ? 'active' : 'inactive',
                    'payment_link' => $link['link'],
                    'amount' => $link['amount'],
                    'total_payments' => $payables->count(),
                    'created_at' => date('Y-m-d h:i a', $link['createdAt'] / 1000),
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

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);

            $payment = Payment::where('id', $payment)
                ->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at')
                ->with([
                    'payable' => function ($query) {
                        $query->select('id', 'type', 'type_id', 'amount');
                    }
                ], 'paymentDetails')->first();

            $model = $payment->created_by_type;
            $user = $model::find($payment->created_by);
            if ($response['code'] == 200) {
                $link = $response['link'];
                $payment_detail = $payment->paymentDetails ? $payment->paymentDetails->last() : null;
                $payment_details = [
                    'customer_name' => $payment->created_by_name,
                    'customer_number' => $user->mobile,
                    'payment_type' => $payment_detail->readableMethod,
                    'id' => $payment->id,
                    'payment_code' => '#' . $payment->id,
                    'amount' => $payment->payable->amount,
                    'created_at' => Carbon::parse($payment->created_at)->format('Y-m-d h:i a'),
                    'link' => $link['link'],
                    'link_code' => '#' . $link['linkId'],
                    'purpose' => $link['reason'],
                    'status' => $link['isActive'] == 1 ? 'active' : 'inactive'
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
