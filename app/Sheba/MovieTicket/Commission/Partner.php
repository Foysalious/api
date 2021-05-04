<?php namespace Sheba\MovieTicket\Commission;

use App\Models\MovieTicketOrder;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Cash;
use Sheba\MovieTicket\MovieTicketCommission;

class Partner extends MovieTicketCommission
{
    private $partner;

    /**
     * @param \App\Models\Partner $partner
     * @return Partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function disburse()
    {
        $this->storeAgentsCommission();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $this->deleteMovieTicketExpenseIncome();
    }

    public function disburseNew()
    {
        $this->storeAgentsCommissionNew();
        $this->storeMovieTicketExpenseIncome();
        $this->saleMovieTicket();
    }

    private function storeMovieTicketExpenseIncome()
    {
        /** @var AutomaticEntryRepository $entry
         * @var MovieTicketOrder $order
         */
        list($entry, $order) = $this->initEntry();
        $entry->setHead(AutomaticExpense::MOVIE_TICKET)
            ->setAmount($order->amount - $order->agent_commission)->store();
        $entry->setHead(AutomaticIncomes::MOVIE_TICKET)->setAmount($order->amount)->store();
    }

    private function deleteMovieTicketExpenseIncome()
    {
        /** @var AutomaticEntryRepository $entry
         * @var MovieTicketOrder $order
         */
        list($entry, $order) = $this->initEntry();
        $entry->setHead(AutomaticExpense::MOVIE_TICKET)->delete();
        $entry->setHead(AutomaticIncomes::MOVIE_TICKET)->delete();
    }

    private function initEntry()
    {

        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $partner
         */
        $partner = $this->agent;
        $entry = app(AutomaticEntryRepository::class);
        $order = $this->movieTicketOrder;
        $entry = $entry->setPartner($partner)
            ->setSourceType(class_basename($order))
            ->setSourceId($order->id);
        return [$entry, $order];
    }

    private function saleMovieTicket()
    {
        $transaction = $this->getWalletTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(Cash::CASH)
            ->setCreditAccountKey(AutomaticIncomes::MOVIE_TICKET)
            ->setDetails("Movie Ticket for sale.")
            ->setReference($transaction->id)
            ->store();
    }

    public function getWalletTransaction()
    {
        return $this->wallet_transaction;
    }
}
