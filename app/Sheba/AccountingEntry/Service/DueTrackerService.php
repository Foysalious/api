<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Entry\Creator as EntryCreator;
use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Entry\Updater as EntryUpdater;
use App\Sheba\AccountingEntry\Repository\DueTrackerReminderRepository;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\AccountingEntry\Service\DueTrackerReportService;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\ContactDoesNotExistInDueTracker;

class DueTrackerService
{
    protected $partner;
    protected $dueTrackerRepo;
    protected $dueTrackerReport;
    protected $contact_type;
    protected $order;
    protected $order_by;
    protected $balance_type;
    protected $limit;
    protected $offset;
    protected $query;
    protected $start_date;
    protected $end_date;
    protected $contact_id;
    /* @var EntryDTO */
    protected $entryDTO;

    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo, DueTrackerReportService $dueTrackerReport)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
        $this->dueTrackerReport =$dueTrackerReport;
    }

    /**
     * @param mixed $entryDTO
     */
    public function setEntryDTO(EntryDTO $entryDTO)
    {
        $this->entryDTO = $entryDTO;
        return $this;
    }

    /**
     * @param $contact_id
     * @return $this
     */
    public function setContactId($contact_id): DueTrackerService
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return DueTrackerService
     */
    public function setPartner($partner): DueTrackerService
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $contact_type
     * @return DueTrackerService
     */
    public function setContactType($contact_type): DueTrackerService
    {
        $this->contact_type = $contact_type;
        return $this;
    }

    /**
     * @param mixed $order
     * @return DueTrackerService
     */
    public function setOrder($order): DueTrackerService
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $order_by
     * @return DueTrackerService
     */
    public function setOrderBy($order_by): DueTrackerService
    {
        $this->order_by = $order_by;
        return $this;
    }

    /**
     * @param mixed $balance_type
     * @return DueTrackerService
     */
    public function setBalanceType($balance_type): DueTrackerService
    {
        $this->balance_type = $balance_type;
        return $this;
    }

    /**
     * @param mixed $limit
     * @return DueTrackerService
     */
    public function setLimit($limit): DueTrackerService
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return DueTrackerService
     */
    public function setOffset($offset): DueTrackerService
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $query
     * @return DueTrackerService
     */
    public function setQuery($query): DueTrackerService
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param $start_date
     * @return $this
     */
    public function setStartDate($start_date): DueTrackerService
    {
        $this->start_date = $start_date;
        return $this;
    }

    /**
     * @param $end_date
     * @return $this
     */
    public function setEndDate($end_date): DueTrackerService
    {
        $this->end_date = $end_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function storeEntry()
    {
        return app()->make(EntryCreator::class)
            ->setEntryDto($this->entryDTO)
            ->setPartner($this->partner)
            ->createEntry();
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function badDebts(): string
    {
        $queryString = $this->generateQueryString();
        $balance = $this->dueTrackerRepo->setPartner($this->partner)->dueListBalanceByContact($this->contact_id, $queryString);
        if($balance['stats']['type'] == 'receivable') {
            $this->entryDTO->setAmount($balance['stats']['balance']);
            /* @var $creator EntryCreator */
            $creator = app()->make(EntryCreator::class);
            $creator->setEntryDto($this->entryDTO)
                ->setPartner($this->partner)
                ->createEntry();
            return "Successful";
        }
        return "Balance is Already Positive.";
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDueListBalance($queryString);
        $return_data = $result;
        $return_data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');
        if ($this->contact_type == ContactType::SUPPLIER) {
            $supplier_due = $this->dueTrackerRepo->setPartner($this->partner)->getSupplierMonthlyDue();
            $return_data['supplier_current_month_due'] = $supplier_due['due'];
        }
        return $return_data;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueList()
    {
        $queryString = $this->generateQueryString();
        return $this->dueTrackerRepo->setPartner($this->partner)->getDueListFromAcc($queryString);

    }

    /**
     * @return array
     * @throws AccountingEntryServerError|ContactDoesNotExistInDueTracker
     */
    public function dueListBalanceByContact(): array
    {
        $contact_balance = $this->getBalanceByContact();
        if ($this->contact_type == ContactType::SUPPLIER) {
            $supplier_due = $this->dueTrackerRepo
                ->setPartner($this->partner)
                ->getSupplierMonthlyDue($this->contact_id);
            $contact_balance['stats']['supplier_current_month_due'] = $supplier_due['due'];
        }
        $reminder = app()->make(DueTrackerReminderRepository::class)
            ->setPartner($this->partner)->reminderByContact($this->contact_id, $this->contact_type);
        if($reminder == []){
            $reminder = null;
        }

        return [
            'contact_details' => $contact_balance['contact_details'],
            'stats' => $contact_balance['stats'],
            'other_info' => $contact_balance['other_info'],
            'reminder' => $reminder,
            'current_time' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @return array
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     */
    public function dueListByContact(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDuelistByContactId($this->contact_id, $queryString);
        $contact_balance = $this->getBalanceByContact();
        $due_list = $result['list'];
        $due_list = $this->dueTrackerReport->calculate_tathkalin_balance($due_list,$contact_balance["stats"]["balance"]);
        return [
            'list' => $due_list
        ];
    }

    /**
     * @return string
     */
    private function generateQueryString(): string
    {
        $query_strings = [];
        if (isset($this->order_by)) {
            $query_strings [] = 'order_by=' . $this->order_by;
            $query_strings [] = isset($this->order) ? 'order=' . strtolower($this->order) : 'order=desc';
        }

        if (isset($this->balance_type)) {
            $query_strings [] = "balance_type=$this->balance_type&";
        }

        if (isset($this->query)) {
            $query_strings [] = "q=$this->query";
        }

        if (isset($this->start_date) && isset($this->end_date)) {
            $query_strings [] = "start_date=$this->start_date";
            $query_strings [] = "end_date=$this->end_date";
        }

        if (isset($this->limit) && isset($this->offset)) {
            $query_strings [] = "limit=$this->limit";
            $query_strings [] = "offset=$this->offset";
        }

        if (isset($this->contact_type)) {
            $query_strings [] = "contact_type=" . strtolower($this->contact_type);
        }

        if (isset($this->contact_id)) {
            $query_strings [] = "contact_id=" . strtolower($this->contact_id);
        }

        if (isset($this->filter_by_supplier)) {
            $query_strings [] = "filter_by_supplier=" . $this->filter_by_supplier;
        }

        return implode('&', $query_strings);
    }

    /**
     * @throws \Exception
     */
    public function updateEntry()
    {
        return app()->make(EntryUpdater::class)
            ->setEntryDto($this->entryDTO)
            ->setPartner($this->partner)
            ->updateEntry();
    }


    /**
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     */
    public function getBalanceByContact()
    {
        $queryString = $this->generateQueryString();
        $contact_balance =  $this->dueTrackerRepo
            ->setPartner($this->partner)
            ->dueListBalanceByContact($this->contact_id, $queryString);
        if(!isset($contact_balance['contact_details'])) {
            throw new ContactDoesNotExistInDueTracker();
        }
        return $contact_balance;
    }


}