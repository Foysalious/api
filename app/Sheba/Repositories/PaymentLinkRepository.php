<?php namespace Sheba\Repositories;

use App\Models\Payable;
use App\Models\Payment;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\PayableNotFound;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\PaymentLink\Target;
use Sheba\PaymentLink\UrlTransformer;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use stdClass;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private $paymentLinkClient;
    private $paymentLinkTransformer;
    private $urlTransformer;

    /**
     * PaymentLinkRepository constructor.
     * @param PaymentLinkTransformer $paymentLinkTransformer
     * @param UrlTransformer $urlTransformer
     * @param PaymentLinkClient $client
     */
    public function __construct(PaymentLinkTransformer $paymentLinkTransformer, UrlTransformer $urlTransformer, PaymentLinkClient $client)
    {
        parent::__construct();
        $this->paymentLinkClient = $client;
        $this->paymentLinkTransformer = $paymentLinkTransformer;
        $this->urlTransformer = $urlTransformer;

    }

    public function getPaymentLinkList(Request $request)
    {
        return $this->paymentLinkClient->paymentLinkList($request);
    }

    public function getPartnerPaymentLinkList(Request $request)
    {
        return $this->paymentLinkClient->paymentLinkList($request);
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

    /**
     * @param $targets Target[]
     * @return PaymentLinkTransformer[][]
     */
    public function getPaymentLinksGroupedByTargets(array $targets)
    {
        $links = $this->paymentLinkClient->getPaymentLinksByTargets($targets);

        return $this->formatPaymentLinkTransformers($links);
    }

    private function formatPaymentLinkTransformers($links)
    {
        $result = [];
        foreach ($links as $link) {
            $link = (new PaymentLinkTransformer())->setResponse(json_decode(json_encode($link)));
            array_push_on_array($result, $link->getUnresolvedTarget()->toString(), $link);
        }
        return $result;
    }

    /**
     * @param $targets Target[]
     * @return PaymentLinkTransformer[][]
     */
    public function getPaymentLinksByPosOrders(array $targets)
    {
        $links = $this->paymentLinkClient->getPaymentLinksByPosOrders($targets);
        return $this->formatPaymentLinkTransformers($links);
    }

    public function getPaymentLinksByPosOrder($target)
    {
        return $this->getPaymentLinksByPosOrders([$target]);
    }

    public function getActivePaymentLinksByPosOrders(array $targets)
    {
        $links = $this->paymentLinkClient->getActivePaymentLinksByPosOrders($targets);
        return $this->formatPaymentLinkTransformers($links);
    }
    public function getActivePaymentLinkByPosOrder($target)
    {
        $links = $this->paymentLinkClient->getActivePaymentLinkByPosOrder($target);
        $payment_link =  $this->formatPaymentLinkTransformers($links);
        $key = $target->toString();
        if (array_key_exists($key, $payment_link)) {
            return $payment_link[$key][0];
        }
        return false;
    }
}
