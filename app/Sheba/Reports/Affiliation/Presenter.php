<?php namespace Sheba\Reports\Affiliation;

use App\Models\Affiliation;
use App\Models\AffiliationReport;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    private $affiliation;
    private $affiliationReport;

    public function setAffiliation(Affiliation $affiliation)
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    public function setAffiliationReport(AffiliationReport $affiliation_report)
    {
        $this->affiliationReport = $affiliation_report;
        return $this;
    }

    /** @return array */
    public function get()
    {
        return $this->affiliationReport ?
            $this->getFromAffiliationReport() :
            $this->getFromAffiliation();
    }

    private function getFromAffiliation()
    {
        $first_log = $this->affiliation->logs->first();
        $first_status_log = $this->affiliation->statusChangeLogs->first();
        if (!$first_log && !$first_status_log) $first_action = null;
        else if (!$first_log && $first_status_log) $first_action = $first_status_log;
        else if ($first_log && !$first_status_log) $first_action = $first_log;
        else if ($first_log && $first_status_log) $first_action = $first_log->created_at->gte($first_status_log->created_at) ? $first_status_log : $first_log;
        $conversion_log = $this->affiliation->statusChangeLogs->where('to_status', 'converted')->first();

        return [
            'ID' => $this->affiliation->id,
            'Agent ID' => $this->affiliation->affiliate->id,
            'Agent Name' => $this->affiliation->affiliate->profile->name,
            'Ambassador ID' => $this->affiliation->affiliate->ambassador_id,
            'Tags' => !$this->affiliation->affiliate->tag_names->isEmpty() ? implode(', ', $this->affiliation->affiliate->tag_names->toArray()) : 'N/A',
            'Customer Name' => $this->affiliation->customer_name,
            'Customer Mobile' => $this->affiliation->customer_mobile,
            'Service Name' => $this->affiliation->service,
            'Conversion Status' => $this->affiliation->status,
            'Order No' => $this->affiliation->order ? $this->affiliation->order->code() : null,
            'Fake Status' => $this->affiliation->is_fake ? true : false,
            'Reject Reason' => $this->affiliation->reject_reason ?: null,
            'Total Sales' => $this->affiliation->order ? $this->affiliation->order->calculate(true)->totalPrice : 0,
            'Commission Received by Agent' => $this->affiliation->order && $this->affiliation->order->affiliation_cost ? $this->affiliation->order->affiliation_cost : 0,
            'First Response time' => $first_action ? $first_action->created_at->diffInMinutes($this->affiliation->created_at) : null,
            'Converter Name' => $conversion_log ? $conversion_log->created_by_name : null,
            'Conversion Time' => $conversion_log ? $conversion_log->created_at->diffInMinutes($this->affiliation->created_at) : 0,
            'Created At' => $this->affiliation->created_at,
            'Updated At' => $this->affiliation->updated_at
        ];
    }

    private function getFromAffiliationReport()
    {
        return [
            'ID' => $this->affiliationReport->id,
            'Agent ID' => $this->affiliationReport->agent_id,
            'Agent Name' => $this->affiliationReport->agent_name,
            'Ambassador ID' => $this->affiliationReport->ambassador_id,
            'Tags' => $this->affiliationReport->tags,
            'Customer Name' => $this->affiliationReport->customer_name,
            'Customer Mobile' => $this->affiliationReport->customer_mobile,
            'Service Name' => $this->affiliationReport->service_name,
            'Conversion Status' => $this->affiliationReport->conversion_status,
            'Order No' => $this->affiliationReport->order_code,
            'Fake Status' => $this->affiliationReport->fake_status,
            'Reject Reason' => $this->affiliationReport->reject_reason,
            'Total Sales' => $this->affiliationReport->total_sales,
            'Commission Received by Agent' => $this->affiliationReport->commission_received_by_agents,
            'First Response time' => $this->affiliationReport->first_response_time,
            'Converter Name' => $this->affiliationReport->converter_name,
            'Conversion Time' => $this->affiliationReport->converter_time,
            'Created At' => $this->affiliationReport->created_at,
            'Updated At' => $this->affiliationReport->updated_at
        ];
    }

    /** @return array */
    public function getForView()
    {
        $data = $this->get();
        $data['Agent Name'] = $data['Agent Name'] ?: 'N/S';
        $data['Ambassador ID'] = $data['Ambassador ID'] ?: 'N/S';
        $data['Customer Name'] = $data['Customer Name'] ?: 'N/S';
        $data['Customer Mobile'] = $data['Customer Mobile'] ? '`' . $data['Customer Mobile'] . '`' : 'N/S';
        $data['Service Name'] = $data['Service Name'] ?: 'N/S';
        $data['Conversion Status'] = $data['Conversion Status'] ?: 'N/S';
        $data['Order No'] = $data['Order No'] ?: 'N/A';
        $data['Fake Status'] = $data['Fake Status'] ? 'Fake' : 'Not Fake';
        $data['Reject Reason'] = $data['Reject Reason'] ?: 'N/S';
        $data['Total Sales'] = $data['Total Sales'] ?: 'N/A';
        $data['Commission Received by Agent'] = $data['Commission Received by Agent'] ?: 'N/A';
        $data['First Response time'] = $data['First Response time'] ?: 'N/A';
        $data['Converter Name'] = $data['Converter Name'] ?: 'N/A';
        $data['Conversion Time'] = $data['Conversion Time'] ?: 'N/A';
        $data['Created At'] = $data['Created At']->format('d M Y h:i A');
        $data['Updated At'] = $data['Updated At']->format('d M Y h:i A');
        return $data;
    }

    public function getForTable()
    {
        $data = $this->get();
        return [
            'agent_id' => $data['Agent ID'],
            'agent_name' => $data['Agent Name'],
            'ambassador_id' => $data['Ambassador ID'],
            'order_code' => $data['Order No'],
            'customer_name' => $data['Customer Name'],
            'customer_mobile' => $data['Customer Mobile'],
            'service_name' => $data['Service Name'],
            'conversion_status' => $data['Conversion Status'],
            'fake_status' => $data['Fake Status'],
            'tags' => $data['Tags'],
            'reject_reason' => $data['Reject Reason'],
            'total_sales' => $data['Total Sales'],
            'commission_received_by_agents' => $data['Commission Received by Agent'],
            'first_response_time' => $data['First Response time'],
            'converter_name' => $data['Converter Name'],
            'converter_time' => $data['Conversion Time'],
            'created_at' => $data['Created At'],
            'updated_at' => $data['Updated At']
        ];
    }
}
