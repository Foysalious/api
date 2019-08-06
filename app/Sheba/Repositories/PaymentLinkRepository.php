<?php namespace Sheba\Repositories;


use App\Models\Payable;
use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\PayableNotFound;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private $paymentLinkClient;
    private $paymentLinkTransformer;

    public function __construct(PaymentLinkTransformer $paymentLinkTransformer)
    {
        parent::__construct();
        $this->paymentLinkClient = new PaymentLinkClient();
        $this->paymentLinkTransformer = $paymentLinkTransformer;

    }

    public function getPaymentLinkList(Request $request)
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
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        return Payable::whereHas('payment', function ($query) {
            $query->where('status', 'completed');
        })->where([
            ['type', 'payment_link'],
            ['type_id', $payment_link_details['linkId']],
        ])->with(['payment' => function ($q) {
            $q->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at');
        }])->select('id', 'type', 'type_id', 'amount');
    }

    public function payment($payment)
    {
        return Payment::where('id', $payment)
            ->select('id', 'payable_id', 'status', 'created_by_type', 'created_by', 'created_by_name', 'created_at')
            ->with([
                'payable' => function ($query) {
                    $query->select('id', 'type', 'type_id', 'amount', 'user_type', 'user_id');
                }
            ], 'paymentDetails')->first();
    }

    /**
     * @param $linkId
     * @return mixed
     */
    public function getPaymentLinkByLinkId($linkId)
    {
        return $this->paymentLinkClient->getPaymentLinkByLinkId($linkId);
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
     * @return PaymentLinkTransformer
     */
    public function findByIdentifier($identifier)
    {
        return $this->paymentLinkTransformer->setResponse($this->paymentLinkClient->getPaymentLinkByIdentifier($identifier));
    }

}
