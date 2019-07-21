<?php namespace Sheba\Repositories;


use App\Models\Payable;
use App\Sheba\Payment\Exceptions\PayableNotFound;
use GuzzleHttp\Client;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private $paymentLinkClient;

    public function __construct(PaymentLinkClient $payment_link_client)
    {
        parent::__construct();
        $this->paymentLinkClient = $payment_link_client;
    }

    public function getPaymentLinkDetails($userId, $userType, $identifier)
    {
        return $this->paymentLinkClient->getPaymentLinkDetails($userId, $userType, $identifier);
    }

    public function create(array $attributes)
    {
        return $this->paymentLinkClient->storePaymentLink($attributes);
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

}
