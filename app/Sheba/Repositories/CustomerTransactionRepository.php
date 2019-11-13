<?php namespace Sheba\Repositories;

use App\Models\Customer;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class CustomerTransactionRepository
{
    private $customer;

    function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param $data
     * @param null $tags
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Exception
     */
    public function save($data, $tags = null)
    {
        $transaction = null;
        if ($data['amount'] > 0) {
            /*
             * WALLET TRANSACTION NEED TO REMOVE
             *  $data['created_at'] = Carbon::now();
             $transaction = $this->customer->transactions()->save(new CustomerTransaction($data));
             (new CustomerRepository())->updateWallet($this->customer, $data['amount'], $data['type']);*/
            // if (is_array($tags) && !empty($tags[0])) $transaction->tags()->sync($tags);
            $transaction = (new WalletTransactionHandler())->setModel($this->customer)->setSource($data['source'])
                ->setType(strtolower($data['type']))->setAmount($data['amount'])->setLog($data['log']);
            if (isset($data['transaction_details'])) {
                $transaction = $transaction->setTransactionDetails($data['transaction_details']);
            }
            $transaction = $transaction->store($this->setExtras($data));
        }
        return $transaction;
    }

    private function setExtras($data)
    {
        unset($data['amount']);
        unset($data['log']);
        unset($data['type']);
        unset($data['source']);
        if (isset($data['transaction_details'])) unset($data['transaction_details']);
        return $data;
    }
}
