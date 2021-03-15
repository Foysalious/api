<?php namespace Sheba\Repositories;

use App\Models\Payable;
use App\Models\Payment;
use App\Transformers\PaymentLinkTransactionDetailsTransformer;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Payment\Exceptions\PayableNotFound;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\PaymentLink\UrlTransformer;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use stdClass;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private $paymentLinkClient;
    private $paymentLinkTransformer;
    private $urlTransformer;
    /**
     * @var PaymentLinkTransactionDetailsTransformer
     */
    private $paymentLinkTransactionDetailTransformer;

    /**
     * PaymentLinkRepository constructor.
     * @param PaymentLinkTransformer $paymentLinkTransformer
     * @param UrlTransformer $urlTransformer
     */
    public function __construct(PaymentLinkTransformer $paymentLinkTransformer, UrlTransformer $urlTransformer)
    {
        parent::__construct();
        $this->paymentLinkClient = new PaymentLinkClient();
        $this->paymentLinkTransformer = $paymentLinkTransformer;
        $this->urlTransformer = $urlTransformer;
        $this->paymentLinkTransactionDetailTransformer = new PaymentLinkTransactionDetailsTransformer();

    }

    public function getPaymentLinkList(Request $request)
    {
        return $this->paymentLinkClient->paymentLinkList($request);
    }

    public function getPartnerPaymentLinkList(Request $request)
    {
        return $this->paymentLinkClient->partnerPaymentLinkList($request);
    }

    /**
     * @param $userId
     * @param $userType
     * @param $identifier
     * @return mixed
     * @throws PayableNotFound
     */
    public function getPaymentLinkDetails($userId, $userType, $identifier)
    {
        return $this->paymentLinkClient->getPaymentLinkDetails($userId, $userType, $identifier);
    }

    /**
     * @param array $attributes
     * @return stdClass|null
     * @throws GuzzleException
     */
    public function create(array $attributes)
    {
        return $this->paymentLinkClient->storePaymentLink($attributes);
    }

    public function statusUpdate($link, $status)
    {
        return $this->paymentLinkClient->paymentLinkStatusChange($link, $status);
    }

    public function paymentLinkDetails($id)
    {
        return $this->paymentLinkClient->paymentLinkDetails($id);
    }

    public function payables($payment_link_details)
    {
        return Payable::whereHas('payments', function ($query) {
            $query->where('status', 'completed');
        })->where([
            ['type', 'payment_link'], ['type_id', $payment_link_details['linkId']],
        ])->with([
            'payments' => function ($q) {
                $q->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at');
            }
        ])->select('id', 'type', 'type_id', 'amount')->orderBy('created_at', 'desc');
    }

    public function payment($payment)
    {
        return Payment::where('id', $payment)->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at')->with([
            'payable' => function ($query) {
                $query->select('id', 'type', 'type_id', 'amount', 'user_type', 'user_id', 'description');
            }
        ], 'paymentDetails')->first();
    }

    /**
     * @param $linkId
     * @return PaymentLinkTransformer|null
     */
    public function find($linkId)
    {
        $response = $this->paymentLinkClient->getPaymentLinkByLinkId($linkId);
        $response = json_decode(json_encode($response['links'][0]));
        return $response ? $this->paymentLinkTransformer->setResponse($response) : null;
    }

    /**
     * @param $id
     * @param string $type
     * @return mixed
     */
    public function getPaymentLinkByTargetIdType($id, $type = "pos_order")
    {
        return $this->paymentLinkClient->getPaymentLinkByTargetIdType($id, $type);
    }

    /**
     * @param $identifier
     * @return PaymentLinkTransformer|null
     */
    public function findByIdentifier($identifier)
    {
        $response = json_decode(json_encode($this->paymentLinkClient->getPaymentLinkByIdentifier($identifier)));
        return $response ? $this->paymentLinkTransformer->setResponse($response) : null;
    }

    public function createShortUrl($url)
    {
        $response = json_decode(json_encode($this->paymentLinkClient->createShortUrl($url)));
        return $response && $response->code == 200 ? $this->urlTransformer->setResponse($response->url) : null;
    }

    public function getPaymentList(Request $request)
    {
        $filter = $search_value = $request->transaction_search;
        $payment_links_list = $this->getPartnerPaymentLinkList($request);
        if (is_array($payment_links_list) && count($payment_links_list) > 0) {
            $transactionList = [];
            $linkIds = array_column($payment_links_list, 'linkId');
            $links = [];
            array_walk($payment_links_list, function ($val, $key) use (&$links) {
                $links[$val['linkId']] = $val;
            });
            $transactionQuery = DB::table('payments as pa')
                ->select('pa.id as payment_id', 'pb.id as payable_id', 'customerProfile.name', 'pb.type_id')
                ->join('payables as pb', 'pa.payable_id', '=', 'pb.id')
                ->leftJoin('customers as cus', function ($join) {
                    $join->on('pb.user_id', '=', 'cus.id')
                        ->on('pb.user_type', '=', DB::raw('"App\\\Models\\\Customer"'));
                })
                ->leftJoin('profiles as customerProfile', function ($join) {
                    $join->on('cus.profile_id', '=', 'customerProfile.id')
                        ->on('pb.user_type', '=', DB::raw('"App\\\Models\\\Customer"'));
                })
                ->whereIn('type_id', $linkIds)
                ->where('type', 'payment_link');

            $transactions = $transactionQuery->get();

            foreach ($transactions as $transaction) {
                $payment = $this->payment($transaction->payment_id);
                $transactionFormatted = $this->paymentLinkTransactionDetailTransformer->transform($payment, $links[$transaction->type_id]);
                array_push($transactionList, $transactionFormatted);
            }


            if ($filter) {
                $transactionList = array_filter($transactionList, function ($item) use ($filter) {
                    return preg_match("/$filter/i", (string)$item['link_id']) || preg_match("/$filter/i", $item['customer_name']);
                });
            }

            usort($transactionList, function ($a, $b) {
                return $b['payment_id'] - $a['payment_id'];
            });

            $limit = $request->transaction_limit;
            $offset = $request->transaction_offset;
            $links = collect($transactionList)->slice($offset)->take($limit);
            return array_values($links->toArray());

        }
        return null;
    }

}
