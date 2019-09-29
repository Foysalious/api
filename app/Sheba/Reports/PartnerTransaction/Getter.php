<?php namespace Sheba\Reports\PartnerTransaction;

use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

abstract class Getter extends ReportData
{
    /** @var Presenter */
    protected $presenter;

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function get(Request $request)
    {
        return $this->map($this->_get($request))->toArray();
    }

    /**
     * @param Request $request
     * @return Collection
     */
    abstract protected function _get(Request $request);

    protected function addCommonProperties(PartnerTransaction $transaction)
    {
        $transaction->gateway = null;
        $transaction->sender = null;
        $transaction->gateway_transaction_id = null;
        if ($transaction->transaction_details) {
            $transaction_details = json_decode($transaction->transaction_details, true);
            if (isset($transaction_details['gateway'])) {
                $transaction->gateway = $transaction_details['gateway'];
                if ($transaction->gateway == 'bkash') {
                    $transaction->gateway_transaction_id = $transaction_details['transaction']['id'];
                    $transaction->sender = $transaction_details['account']['number'];
                }
            }
        }

        $transaction->tags = $transaction->getTagNamesAttribute();
        return $transaction;
    }

    /**
     * @param PartnerTransaction $transactions
     * @return PartnerTransaction
     */
    abstract protected function addProperties(PartnerTransaction $transactions);

    /**
     * @param Collection $partner_transactions
     * @return Collection
     */
    protected function map(Collection $partner_transactions)
    {
        return $partner_transactions->map(function (PartnerTransaction $transaction) {
            $this->addCommonProperties($transaction);
            $this->addProperties($transaction);
            return $this->presenter->setPartnerTransaction($transaction)->getForView();
        });
    }
}
