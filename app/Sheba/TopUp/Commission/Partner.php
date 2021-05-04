<?php namespace Sheba\TopUp\Commission;

use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Cash;
use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    private $partner;
    private $topUpDisburse;

    public function __construct(TopUpCommission $topUpCommission)
    {
        $this->topUpDisburse =$topUpCommission;
    }

    /**
     * @param \App\Models\Partner $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeExpenseIncome();
        $this->saleTopUp();
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

    private function saleTopUp()
    {
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($this->topUpDisburse->getTransaction())
            ->setAmount($this->topUpDisburse->amount)
            ->setDebitAccountKey(Cash::CASH)
            ->setCreditAccountKey(AutomaticIncomes::TOP_UP)
            ->setDetails("Top Up for sale")
            ->setReference("TopUp selling amount is" . $this->topUpDisburse->amount . " tk.")
            ->store();
    }
}
