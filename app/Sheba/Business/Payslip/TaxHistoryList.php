<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use App\Transformers\Business\PayRunListTransformer;
use App\Transformers\Business\TaxHistoryListTransformer;
use Illuminate\Support\Facades\DB;
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
    private $search;
    private $departmentID;

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

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @param $department_id
     * @return $this
     */
    public function setDepartmentID($department_id)
    {
        $this->departmentID = $department_id;
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
        if ($this->departmentID) $tax_history = $this->filterByDepartment($tax_history);
        $this->taxHistoryList = $tax_history->get();
    }

    private function getData()
    {
        $profiles = $this->getBusinessMembersProfileName();
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $tax_history_list = new Collection($this->taxHistoryList, new TaxHistoryListTransformer($profiles));
        return collect($manager->createData($tax_history_list)->toArray()['data']);
    }

    /**
     * @param $payslips
     * @return mixed
     */
    private function filterByDepartment($payslips)
    {
        return $payslips->whereHas('businessMember', function ($q) {
            $q->whereHas('role', function ($q) {
                $q->whereHas('businessDepartment', function ($q) {
                    $q->where('business_departments.id', $this->departmentID);
                });
            });
        });
    }

    private function getBusinessMembersProfileName()
    {
        return DB::table('business_member')
            ->join('members', 'members.id', '=', 'business_member.member_id')
            ->join('profiles', 'profiles.id', '=', 'members.profile_id')
            ->whereIn('business_member.id', $this->businessMemberIds)->pluck('name', 'business_member.id');
    }

}