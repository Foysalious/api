<?php namespace Sheba\PartnerWallet;

use App\Models\Partner;
use App\Models\PartnerOrder;
use Sheba\Repositories\PartnerTransactionRepository;

class PartnerTransactionHandler
{
    private $partnerTransactionRepo;

    function __construct(Partner $partner)
    {
        $this->partnerTransactionRepo = new PartnerTransactionRepository($partner);
    }

    public function credit($amount, $log, PartnerOrder $partner_order = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Credit';
        $this->partnerTransactionRepo->save($data);
    }

    public function debit($amount, $log, PartnerOrder $partner_order = null)
    {
        $data = $this->formatData($amount, $log, $partner_order);
        $data['type'] = 'Debit';
        $this->partnerTransactionRepo->save($data);
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