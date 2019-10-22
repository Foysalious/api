<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Transport\Bus\BusTicketCommission;

class Partner extends BusTicketCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeIncomeExpense();
    }

    private function storeIncomeExpense()
    {
        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $agent
         */
        list($order, $entry) = $this->initEntry();
        $entry->setHead(AutomaticIncomes::BUS_TICKET)->setAmount($order->amount)->store();
        $entry->setHead(AutomaticExpense::BUS_TICKET)->setAmount($order->amount - $order->agent_amount)->store();
    }

    public function refund()
    {
        $this->deleteIncomeExpense();
    }

    private function deleteIncomeExpense()
    {
        /** @var AutomaticEntryRepository $entry
         * @var \App\Models\Partner $agent
         */
        list($order, $entry) = $this->initEntry();
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
}
