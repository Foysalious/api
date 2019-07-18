<?php namespace App\Http\Controllers\PaymentLink;

use App\Models\Payable;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

class PaymentLinkController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links';
            $url = "$url?userType=$user_type&userId=$user_id";
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);

            $payment_links = [];
            if ($response['code'] == 200) {
                list($offset, $limit) = calculatePagination($request);
                $links = collect($response['links'])->slice($offset)->take($limit);
                foreach ($links as $link) {
                    $link = [
                        'id' => $link['linkId'],
                        'code' => '#' . $link['linkId'],
                        'purpose' => $link['reason'],
                        'status' => $link['status'],
                        'amount' => $link['amount'],
                        'created_at' => date('Y-m-d h:i a', $link['createdAt'] / 1000),
                    ];
                    array_push($payment_links, $link);
                }
                return api_response($request, $payment_links, 200, ['payment_links' => $payment_links]);
            } elseif ($response['code'] == 404) {
                return api_response($request, 1, 404);
            } else {
                return api_response($request, null, 500);
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
                    'status' => $result->link->status,
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
            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $url = "$url?status=$request->status";
            $client = new Client();
            $result = $client->request('PUT', $url, []);
            $result = json_decode($result->getBody());
            if ($result->code == 200) {
                $payment_link = [
                    'reason' => $result->link->reason,
                    'type' => $result->link->type,
                    'status' => $result->link->status,
                    'amount' => $result->link->amount,
                ];
                return api_response($request, $payment_link, 200, ['payment_link' => $payment_link]);
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
                ])->select('id', 'type', 'type_id', 'amount')->with([
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
                    'status' => $link['status'],
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

    public function paymentLinkPaymentDetails($payment, Request $request)
    {
        try {
            if (1) {
                $payment_details = [
                    'id' => 1,
                    'payment_code' => '#156412',
                    'customer_name' => 'Sabbir',
                    'customer_number' => '01678099565',
                    'link_code' => '#P-123456',
                    'link' => 'https://sheba.xyz/p/@VenusBeauty',
                    'purpose' => 'Mobile home delivery',
                    'payment_type' => 'Bkash',
                    'amount' => 220,
                    'created_at' => Carbon::parse('2019-07-18 18:05:51')->format('Y-m-d h:i a'),
                    'tnx_id' => 24359487,
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
