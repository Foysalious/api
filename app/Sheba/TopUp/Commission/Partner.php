<?php namespace Sheba\TopUp\Commission;

use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeExpenseIncome();
    }

    private function storeExpenseIncome()
    {
        $income = $this->amount;
        $cost = $this->amount - $this->topUpOrder->agent_commission;
        /** @var \App\Models\Partner $partner */
        $partner = $this->agent;
        /** @var AutomaticEntryRepository $entryRepo */
        $entryRepo = app(AutomaticEntryRepository::class);
        $entryRepo = $entryRepo->setPartner($partner)->setSourceType(class_basename($this->topUpOrder))->setSourceId($this->topUpOrder->id);
        $entryRepo->setAmount($income)->setHead(AutomaticIncomes::TOP_UP)->store();
        $entryRepo->setAmount($cost)->setHead(AutomaticExpense::TOP_UP)->store();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $this->deleteExpenseIncome();
    }

    private function deleteExpenseIncome()
    {

        /** @var \App\Models\Partner $partner */
        $partner = $this->agent;
        /** @var AutomaticEntryRepository $entryRepo */
        $entryRepo = app(AutomaticEntryRepository::class);
        $entryRepo = $entryRepo->setPartner($partner)->setSourceType(class_basename($this->topUpOrder))->setSourceId($this->topUpOrder->id);
        $entryRepo->delete();
    }
}
