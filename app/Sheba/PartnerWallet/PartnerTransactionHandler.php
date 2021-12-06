<?php namespace Sheba\PartnerWallet;

use App\Models\Partner;
use App\Models\PartnerOrder;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Sheba\Repositories\PartnerTransactionRepository;

class PartnerTransactionHandler
{
    private $partnerTransactionRepo;

    function __construct(Partner $partner)
    {
        $this->partnerTransactionRepo = new PartnerTransactionRepository($partner);
    }

    /**
     * @param $amount
     * @param $log
     * @param PartnerOrder|null $partner_order
     * @param null $tags
     * @throws Exception
     */
    public function credit($amount, $log, PartnerOrder $partner_order = null, $tags = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Credit';
        return $this->partnerTransactionRepo->save($data, $tags);
    }

    /**
     * @param $amount
     * @param $log
     * @param PartnerOrder|null $partner_order
     * @param null $tags
     * @return Model|null
     * @throws Exception
     */
    public function debit($amount, $log, PartnerOrder $partner_order = null, $tags = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Debit';
        return $this->partnerTransactionRepo->save($data, $tags);
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