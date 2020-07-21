<?php namespace Sheba\Vendor;

use App\Models\PartnerOrder;
use App\Models\Vendor;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class VendorTransactionHandler
{
    /** @var Vendor */
    private $vendor;

    public function setVendor(Vendor $vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    public function credit($amount, $against = null, $log = "")
    {
        $this->save("Credit", $amount, $against, $log);
    }

    private function save($type, $amount, $against = null, $log = "")
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->vendor->walletTransaction([
            'amount' => $amount,
            'type' => $type,
            'initiator_type' => $against ? get_class($against) : "",
            'initiator_id' => $against ? $against->id : "",
            'log' => $log
        ]);*/

        (new WalletTransactionHandler())
            ->setModel($this->vendor)
            ->setSource($this->getSource($against))
            ->setType(strtolower($type))
            ->setAmount($amount)
            ->setLog($log)
            ->dispatch([
                'initiator_type' => $against ? get_class($against) : "",
                'initiator_id' => $against ? $against->id : ""
            ]);

    }

    private function getSource($against = null)
    {
        if ($against && $against instanceof PartnerOrder) return TransactionSources::SERVICE_PURCHASE;
        else return TransactionSources::SHEBA_WALLET;
    }

    public function debit($amount, $against = null, $log = "")
    {
        $this->save("Debit", $amount, $against, $log);
    }
}
