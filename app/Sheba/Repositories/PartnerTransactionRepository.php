<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Carbon\Carbon;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class PartnerTransactionRepository
{
    private $partner;

    function __construct(Partner $partner)
    {
        $this->partner = $partner;
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
            $data['created_at'] = Carbon::now();
            /*
             * WALLET TRANSACTION NEED TO REMOVE
             *  $transaction = $this->partner->transactions()->save(new PartnerTransaction($data));
             (new PartnerRepository(new Partner()))->updateWallet($this->partner, $data['amount'], $data['type']);*/
            $transaction = (new WalletTransactionHandler())->setModel($this->partner)->setSource(TransactionSources::SERVICE_PURCHASE)
                ->setType(strtolower($data['type']))->setAmount($data['amount'])->setLog($data['log']);
            if (isset($data['transaction_details'])) {
                $transaction = $transaction->setTransactionDetails($data['transaction_details']);
            }
            $transaction = $transaction->store($this->setExtras($data));
            if (is_array($tags) && !empty($tags[0])) $transaction->tags()->sync($tags);
        }
        return $transaction;
    }

    private function setExtras($data)
    {
        unset($data['amount']);
        unset($data['log']);
        unset($data['type']);
        if (isset($data['transaction_details'])) unset($data['transaction_details']);
        return $data;
    }

    public function hasSameDetails($details)
    {
        $details = json_decode($details, 1);
        $gateway = $details['gateway'];
        $transaction_id = $details['transaction']['id'];
        return PartnerTransaction::where('transaction_details', 'LIKE', '%"gateway":"' . $gateway . '"%')
                ->where('transaction_details', 'LIKE', '%"transaction":{"id":"' . $transaction_id . '"%')
                ->count() > 0;
    }

    public function thisMonthTotalPaymentLinkCredit()
    {
        $start_date = Carbon::now()->startOfMonth()->toDateTimeString();
        $end_date   = Carbon::now()->toDateTimeString();
        return PartnerTransaction::paymentLinkCredit()->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
    }
}
