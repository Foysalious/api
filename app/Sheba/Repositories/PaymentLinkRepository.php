<?php namespace Sheba\Repositories;


use App\Models\Payable;
use App\Models\Payment;
use Sheba\PaymentLink\PaymentLinkClient;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private $paymentLinkClient;

    public function __construct()
    {
        $this->paymentLinkClient = new PaymentLinkClient();
        parent::__construct();
    }

    public function getPaymentLinkDetails($userId, $userType, $identifier)
    {
        return $this->paymentLinkClient->getPaymentLinkDetails($userId, $userType, $identifier);
    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     * @method PaymentLinkRepository create
     * @override
     */
    public function create(array $attributes)
    {
        return $this->paymentLinkClient->storePaymentLink($attributes);
    }

    public function statusUpdate($link, array $data)
    {
        return $this->paymentLinkClient->paymentLinkStatusChange($link, $data);
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
                    $query->select('id', 'type', 'type_id', 'amount');
                }
            ], 'paymentDetails')->first();
    }

}
