<?php namespace Sheba\Reports;

class ReportForComplains
{
    private $file_name;
    private $complains;
    private $columns;
    private $complains_report;

    private $excel;

    /**
     * ReportForComplains constructor.
     * @param ExcelHandler $excel
     * @param $complains
     */
    public function __construct(ExcelHandler $excel, $complains)
    {
        $this->complains        = $complains;
        $this->columns          = array_reduce(constants('COMPLAIN_CATEGORIES'), 'array_merge', array());
        $this->complains_report = [];
        $this->file_name        = "ComplainAnalysisReport-";
        $this->excel            = $excel;
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function raw($name)
    {
        $filename = $this->file_name . $name;
        $this->excel->setName($this->file_name . $name);
        $this->excel->setViewFile('complain_analysis');
        $this->excel->pushData('complains_report', $this->complains_report)->pushData('columns', $this->columns);
        $this->excel->download();
    }

    /**
     * @throws \Exception
     */
    public function crmVsComplain()
    {
        foreach($this->complains as $complain) {
            $crm = $complain->complainable->crm ? $complain->complainable->crm->name : "N/S";
            $this->_initializeRowIfEmpty($crm, $complain->category);
            $this->complains_report[$crm][$complain->category]++;
        }
        $this->raw('CRM Vs Complain');
    }

    /**
     * @throws \Exception
     */
    public function spVsComplain()
    {
        foreach($this->complains as $complain) {
            $this->_initializeRowIfEmpty($complain->complainable->partnerOrder->partner->name, $complain->category);
            $this->complains_report[$complain->complainable->partnerOrder->partner->name][$complain->category]++;
        }
        $this->raw('SP Vs Complain');
    }

    /**
     * @throws \Exception
     */
    public function deptVsComplain()
    {
        foreach($this->complains as $complain) {
            $this->_initializeRowIfEmpty($complain->complainable->partnerOrder->order->shortChannel(), $complain->category);
            $this->complains_report[$complain->complainable->partnerOrder->order->shortChannel()][$complain->category]++;
        }

        $this->raw('Department Vs Complain');
    }

    /**
     * @throws \Exception
     */
    public function serviceVsComplain()
    {
        foreach($this->complains as $complain) {
            $this->_initializeRowIfEmpty($complain->complainable->service->name, $complain->category);
            $this->complains_report[$complain->complainable->service->name][$complain->category]++;
        }
        $this->raw('Service Vs Complain');
    }

    /**
     * @throws \Exception
     */
    public function spVsDept()
    {
        $this->columns = array_unique(array_values(getSalesChannels('short_name')));
        foreach($this->complains as $complain) {
            $this->_initializeRowIfEmpty($complain->group, $complain->complainable->partnerOrder->order->shortChannel());
            $this->complains_report[$complain->group][$complain->complainable->partnerOrder->order->shortChannel()]++;
        }
        $this->raw('Complain Group Vs Department');
    }

    private function _initializeRowIfEmpty($row, $column) {
        if(!isset($this->complains_report[$row])) {
            $this->complains_report[$row] = [];
        }
        $this->_initializeColumnIfEmpty($row, $column);
    }

    private function _initializeColumnIfEmpty($row, $column) {
        if(!isset($this->complains_report[$row][$column])) {
            $this->complains_report[$row][$column] = 0;
        }
    }
}