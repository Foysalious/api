<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use App\Transformers\Business\PayRunListTransformer;
use App\Transformers\Business\TaxHistoryListTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class TaxHistoryList
{
    /*** @var Business $business*/
    private $business;
    private $businessMemberIds;
    private $taxHistory;
    /*** @var BusinessMemberRepositoryInterface $businessMemberRepository*/
    private $businessMemberRepository;
    /*** @var TaxHistoryRepository $taxHistoryRepository*/
    private $taxHistoryRepository;
    private $taxHistoryList;
    private $timePeriod;
    private $sortColumn;
    private $sort;

    public function __construct(BusinessMemberRepositoryInterface $business_member_repository, TaxHistoryRepository $tax_history_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
        $this->taxHistoryRepository = $tax_history_repository;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getActiveBusinessMember()->pluck('id')->toArray();
        return $this;
    }

    public function setTimePeriod($time_period)
    {
        $this->timePeriod = $time_period;
        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSortKey($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = $column;
        return $this;
    }

    public function get()
    {
        $this->runTaxReportQuery();
        $this->taxHistory = $this->getData();
        return $this->taxHistory;
    }

    private function runTaxReportQuery()
    {
        $tax_history = $this->taxHistoryRepository->getTaxReportByBusinessMemberIds($this->businessMemberIds)->orderBy('id', 'DESC');
        if ($this->timePeriod) $tax_history->whereBetween('generated_at', [$this->timePeriod->start, $this->timePeriod->end]);
        $this->taxHistoryList = $tax_history->get();
    }

    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $tax_history_list = new Collection($this->taxHistoryList, new TaxHistoryListTransformer());
        $tax_history_list = collect($manager->createData($tax_history_list)->toArray()['data']);

        if ($this->sort && $this->sortColumn) $tax_history_list = $this->sortByColumn($tax_history_list, $this->sortColumn, $this->sort)->values();
        return $tax_history_list;
    }

    private function sortByColumn($data, $column, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($data)->$sort_by(function ($item) use ($column) {
            return strtoupper($item[$column]);
        });
    }

}