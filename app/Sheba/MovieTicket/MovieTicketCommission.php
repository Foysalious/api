<?php namespace Sheba\MovieTicket;

use App\Models\Affiliate;
use App\Models\MovieTicketOrder;
use App\Models\MovieTicketVendor;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

abstract class MovieTicketCommission
{
    use ModificationFields;

    /** @var MovieTicketOrder */
    protected $movieTicketOrder;
    /** @var MovieAgent */
    protected $agent;
    /** @var MovieTicketVendor */
    protected $vendor;
    /** @var MovieTicketCommission */
    protected $vendorCommission;
    protected $amount;
    protected $transaction;
    protected $amount_after_commission;

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @return $this
     */
    public function setMovieTicketOrder(MovieTicketOrder $movie_ticket_order)
    {
        $this->movieTicketOrder = $movie_ticket_order;
        $this->amount           = $this->movieTicketOrder->amount;

        $this->setAgent($movie_ticket_order->agent)->setMovieTicketVendor($movie_ticket_order->vendor)->setVendorCommission();

        unset($movie_ticket_order->agent);
        unset($movie_ticket_order->vendor);

        return $this;
    }

    /**
     * @return MovieTicketCommission
     */
    protected function setVendorCommission()
    {
        $commissions              = $this->vendor->commissions()->where('type', get_class($this->agent));
        $commissions_copy         = clone $commissions;
        $commission_of_individual = $commissions_copy->where('type_id', $this->agent->id)->first();
        $this->vendorCommission   = $commission_of_individual ?: $commissions->whereNull('type_id')->first();
        return $this;
    }

    /**
     * @param MovieTicketVendor $movieTicketVendor
     * @return MovieTicketCommission
     */
    protected function setMovieTicketVendor(MovieTicketVendor $movieTicketVendor)
    {
        $this->vendor = $movieTicketVendor;
        return $this;
    }

    /**
     * @param MovieAgent $agent
     * @return MovieTicketCommission
     */
    protected function setAgent(MovieAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    abstract public function disburse();

    abstract public function disburseNew();

    abstract public function refund();

    /**
     *
     */
    protected function storeAgentsCommission()
    {
        $this->movieTicketOrder->agent_commission = $this->calculateMovieTicketCommission($this->amount - $this->movieTicketOrder->amount);
        $this->movieTicketOrder->save();

        $transaction = (new MovieTicketTransaction())->setAmount($this->amount - $this->movieTicketOrder->agent_commission)
            ->setLog(($this->amount - $this->movieTicketOrder->agent_commission) . " has been deducted for a movie ticket, of user with mobile number: " . $this->movieTicketOrder->reserver_mobile)
            ->setMovieTicketOrder($this->movieTicketOrder);
        $this->agent->movieTicketTransaction($transaction);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateMovieTicketCommission($amount)
    {
        $commission = (double)$amount * ($this->getVendorAgentCommission() / (100 + $this->getShebaCommission()));
        if ($this->agent instanceof Affiliate) {
            $cap = constants('AFFILIATE_REWARD')['MOVIE']['AGENT']['cap'];
            return min($commission, $cap);
        }
        return $commission;
    }

    /**
     * @return float
     */
    private function getVendorAgentCommission()
    {
        return (double)$this->vendorCommission->agent_commission;
    }

    private function getShebaCommission()
    {
        return (double)$this->vendor->sheba_commission;
    }

    protected function storeAgentsCommissionNew()
    {
        $this->movieTicketOrder->agent_commission = $this->calculateMovieTicketCommission($this->amount);
        $this->movieTicketOrder->save();
        $transaction = (new MovieTicketTransaction())->setAmount($this->movieTicketOrder->agent_commission)
            ->setLog(number_format($this->movieTicketOrder->agent_commission, 2) . " point has been collected from Sheba for movie ticket sales commission. of user with mobile number: " . $this->movieTicketOrder->reserver_mobile)
            ->setMovieTicketOrder($this->movieTicketOrder);
        $this->transaction = $this->agent->movieTicketTransactionNew($transaction);
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateAmbassadorCommissionForMovieTicket($amount)
    {
        $commission = (double)$amount * ($this->getVendorAmbassadorCommission() / (100 + $this->getShebaCommission()));
        if ($this->agent instanceof Affiliate) {
            $cap = constants('AFFILIATE_REWARD')['MOVIE']['AMBASSADOR']['cap'];
            return min($commission, $cap);
        }
        return $commission;
    }

    /**
     * @return float
     */
    private function getVendorAmbassadorCommission()
    {
        return (double)$this->vendorCommission->ambassador_commission;
    }

    protected function refundAgentsCommission()
    {
        $this->setModifier($this->agent);
        $amount                  = $this->movieTicketOrder->amount;
        $this->amount_after_commission = round($amount - $this->calculateMovieTicketCommission($amount), 2);
        $log                     = "Your movie ticket request of TK $amount has failed, TK $this->amount_after_commission is refunded in your account.";
        $this->refundUser($this->amount_after_commission, $log);
    }

    private function refundUser($amount, $log)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->agent->creditWallet($amount);
        $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);*/
        /** @var HasWalletTransaction $model */
        $model = $this->agent;
        $this->transaction = (new WalletTransactionHandler())->setModel($model)->setSource(TransactionSources::MOVIE)->setAmount($amount)
            ->setType(Types::credit())->setLog($log)->store();
    }
}
