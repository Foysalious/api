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

    public function __construct(TopUpCommission $topUpCommission, \App\Models\Partner $partner)
    {
        $this->topUpDisburse =$topUpCommission;
        $this->partner = $partner;
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
        $this->refundTopUp();
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
        $transaction = $this->getTopUpTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(Cash::CASH)
            ->setCreditAccountKey(AutomaticIncomes::TOP_UP)
            ->setDetails("TopUp for sale")
            ->setReference("TopUp sales amount is" . $transaction->amount . " tk.")
            ->store();
    }

    private function refundTopUp()
    {
        $transaction = $this->getTopUpTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(AutomaticExpense::SHEBA_ACCOUNT)
            ->setCreditAccountKey(AutomaticIncomes::GENERAL_REFUNDS)
            ->setDetails("Refund TopUp")
            ->setReference("TopUp refunds amount is" . $transaction->amount . " tk.")
            ->store();
    }

    public function getTopUpTransaction()
    {
        return $this->topUpDisburse->getTransaction();
    }
}
