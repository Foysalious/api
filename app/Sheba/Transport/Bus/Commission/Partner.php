<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys;
use Sheba\Transport\Bus\BusTicketCommission;

class Partner extends BusTicketCommission
{
    private $partner;
    private $busTicketDisburse;

    public function __construct(BusTicketCommission $busTicketCommission, \App\Models\Partner $partner)
    {
        $this->busTicketDisburse =$busTicketCommission;
        $this->partner = $partner;
    }

    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeIncomeExpense();
        $this->saleBusTicket();
        $this->purchaseBusTicketForSale();
    }

    private function storeIncomeExpense()
    {
        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $agent
         */
        list($entry,$order) = $this->initEntry();
        $entry->setHead(AutomaticIncomes::BUS_TICKET)->setAmount($order->amount)->store();
        $entry->setHead(AutomaticExpense::BUS_TICKET)->setAmount($order->amount - $order->agent_amount)->store();
    }

    public function refund()
    {
        $this->deleteIncomeExpense();
        $this->refundBusTicket();
    }

    private function deleteIncomeExpense()
    {
        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $agent
         */
        list( $entry,$order) = $this->initEntry();
        $entry->setHead(AutomaticIncomes::BUS_TICKET)->delete();
        $entry->setHead(AutomaticExpense::BUS_TICKET)->delete();
    }

    /**
     * @return array
     */
    private function initEntry()
    {
        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $agent
         */
        $agent = $this->agent;
        $order = $this->transportTicketOrder;
        $entry = app(AutomaticEntryRepository::class);
        $entry = $entry->setPartner($agent)->setSourceType(class_basename($order))->setSourceId($order->id);
        return [$entry, $order];
    }

    private function saleBusTicket()
    {
        $transaction = $this->getBusTicketTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(AccountKeys\Asset\Cash::CASH)
            ->setCreditAccountKey(AccountKeys\Income\BusTicketSale::BUS_TICKET)
            ->setDetails("Bus Ticket for sale.")
            ->setReference("Bus Ticket selling amount is" . $transaction->amount . " tk.")
            ->store();
    }

    private function purchaseBusTicketForSale()
    {
        $transaction = $this->getBusTicketTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(AccountKeys\Asset\Cash::CASH)
            ->setCreditAccountKey(AccountKeys\Expense\BusTicketPurchase::BUS_TICKET)
            ->setDetails("Purchase Bus Ticket for sale.")
            ->setReference("Bus Ticket purchasing amount is" . $transaction->amount . " tk.")
            ->store();
    }

    private function refundBusTicket()
    {
        $transaction = $this->getBusTicketTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(AccountKeys\Income\Refund::GENERAL_REFUNDS)
            ->setCreditAccountKey(AccountKeys\Asset\Cash::CASH)
            ->setDetails("Refund BusTicket")
            ->setReference("BusTicket refunds amount is" . $transaction->amount . " tk.")
            ->store();
    }

    public function getBusTicketTransaction()
    {
        return $this->busTicketDisburse->getTransaction();
    }
}
