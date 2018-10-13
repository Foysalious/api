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
     * @param null $tags
     * @throws \Exception
     */
    public function credit($amount, $log, $tags = null)
    {
        $data = $this->formatData($amount, $log);
        $data['type'] = 'Credit';
        $this->customerTransactionRepo->save($data, $tags);
    }

    /**
     * @param $amount
     * @param $log
     * @param null $tags
     * @throws \Exception
     */
    public function debit($amount, $log, $tags = null)
    {
        $data = $this->formatData($amount, $log);
        $data['type'] = 'Debit';
        $this->customerTransactionRepo->save($data, $tags);
    }

    private function formatData($amount, $log)
    {
        return [
            'amount' => $amount,
            'log' => $log
        ];
    }
}