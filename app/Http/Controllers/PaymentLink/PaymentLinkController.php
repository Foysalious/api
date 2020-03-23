<?php namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use App\Transformers\PaymentDetailTransformer;
use App\Transformers\PaymentLinkArrayTransform;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\ModificationFields;
use Sheba\PaymentLink\Creator;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;

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
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($identifier, Request $request, PaymentLinkRepositoryInterface $paymentLinkRepository)
    {
        try {
            $link = $paymentLinkRepository->findByIdentifier($identifier);
            if ($link && (int)$link->getIsActive()) {
                $user  = $link->getPaymentReceiver();
                $payer = $link->getPayer();
                return api_response($request, $link, 200, [
                    'link' => [
                        'id'               => $link->getLinkID(),
                        'identifier'       => $link->getLinkIdentifier(),
                        'purpose'          => $link->getReason(),
                        'amount'           => $link->getAmount(),
                        'payment_receiver' => [
                            'name'  => $user->name,
                            'image' => $user->logo
                        ],
                        'payer'            => $payer ? [
                            'name'   => $payer->name,
                            'mobile' => $payer->mobile
                        ] : null
                    ]
                ]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'amount'  => 'required',
                'purpose' => 'required',
            ]);
            $this->creator->setIsDefault($request->isDefault)->setAmount($request->amount)->setReason($request->purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type)->setTargetId($request->pos_order_id)->setTargetType('pos_order');
            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
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

    public function createPaymentLinkForDueCollection(Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required',
            ]);
            $purpose = 'Due Collection';
            $this->creator->setAmount($request->amount)->setReason($purpose)->setUserName($request->user->name)->setUserId($request->user->id)->setUserType($request->type);
            $payment_link_store = $this->creator->save();
            if ($payment_link_store) {
                $payment_link = $this->creator->getPaymentLinkData();
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
                    'link'    => $default_payment_link[0]['link'],
                    'amount'  => $default_payment_link[0]['amount'],
                ];
                return api_response($request, $default_payment_link, 200, ['default_payment_link' => $default_payment_link]);
            } else {
                $request->merge(['isDefault' => 1]);
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
                    $payment = $payable->payment ? $payable->payment : null;
                    $payment = [
                        'id'         => $payment ? $payment->id : null,
                        'code'       => $payment ? '#' . $payment->id : null,
                        'name'       => $payment ? $payment->payable->getName() : null,
                        'amount'     => $payment ? $payable->amount : null,
                        'created_at' => $payment ? Carbon::parse($payment->created_at)->format('Y-m-d h:i a') : null,
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
}
