<?php namespace Sheba\CustomerWallet;

use App\Models\Customer;
use App\Models\PartnerOrder;
use Sheba\Repositories\CustomerTransactionRepository;

class CustomerTransactionHandler
{
    private $customerTransactionRepo;

    function __construct(Customer $customer)
    {
        $this->customerTransactionRepo = new CustomerTransactionRepository($customer);
    }

    /**
     * @param $amount
     * @param $log
     * @param PartnerOrder | null $partner_order
     * @param null $tags
     * @throws \Exception
     */
    public function credit($amount, $log, PartnerOrder $partner_order = null, $tags = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Credit';
        $this->customerTransactionRepo->save($data, $tags);
    }

    /**
     * @param $amount
     * @param $log
     * @param PartnerOrder | null $partner_order
     * @param null $tags
     * @throws \Exception
     */
    public function debit($amount, $log, PartnerOrder $partner_order = null, $tags = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Debit';
        $this->customerTransactionRepo->save($data, $tags);
    }

    private function formatData($amount, $log, PartnerOrder $partner_order = null)
    {
        return [
            'amount' => $amount,
            'log' => $log,
            'partner_order_id' => $partner_order ? $partner_order->id : null,
        ];
    }
}