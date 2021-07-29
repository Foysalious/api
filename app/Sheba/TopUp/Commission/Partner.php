<?php namespace Sheba\TopUp\Commission;

use ReflectionException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys;
use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    /**
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws ReflectionException
     * @throws KeyNotFoundException
     */
    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeExpenseIncome();
        $this->storeTopUpJournal();
    }

    /**
     * @throws ReflectionException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    private function storeTopUpJournal()
    {
        /** @var \App\Models\Partner $partner */
        $partner = $this->agent;
        (new JournalCreateRepository())
            ->setTypeId($partner->id)
            ->setSource($this->transaction)
            ->setAmount($this->transaction->amount)
            ->setDebitAccountKey(AccountKeys\Asset\Cash::CASH)
            ->setCreditAccountKey(AccountKeys\Income\TopUp::TOP_UP)
            ->setDetails("TopUp for sale")
            ->setReference("TopUp sales amount is" . $this->transaction->amount . " tk.")
            ->store();
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
//        $this->refundTopUp();
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

    private function refundTopUp()
    {
        $transaction = $this->getTopUpTransaction();
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(AccountKeys\Asset\Sheba::SHEBA_ACCOUNT)
            ->setCreditAccountKey(AccountKeys\Income\Refund::GENERAL_REFUNDS)
            ->setDetails("Refund TopUp")
            ->setReference("TopUp refunds amount is" . $transaction->amount . " tk.")
            ->store();
    }

    public function getTopUpTransaction()
    {
        return $this->topUpDisburse->getTransaction();
    }
}
