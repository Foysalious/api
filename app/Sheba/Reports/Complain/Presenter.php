<?php namespace Sheba\Reports\Complain;

use App\Models\ComplainReport;
use Carbon\Carbon;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    private $complain;
    private $complainReport;

    public function setComplain(Complain $complain)
    {
        $this->complain = $complain;
        return $this;
    }

    public function setComplainReport(ComplainReport $complain_report)
    {
        $this->complainReport = $complain_report;
        return $this;
    }

    /** @return array */
    public function get()
    {
        return $this->complainReport ?
            $this->getFromComplainReport() :
            $this->getFromComplain();
    }

    private function getFromComplain()
    {
        return [
            'Complain Id' => $this->complain->id,
            'Complain Code' => $this->complain->code(),
            'Complain Status' => $this->complain->status,
            'Created Date' => $this->complain->created_at,
            'Complain' => $this->complain->complain,
            'Complain Category' => $this->complain->preset->complainCategory->name,
            'Complain Preset' => $this->complain->preset->name,
            'Complain Source' => $this->complain->source,
            'Job Code' => $this->complain->job_id ? $this->complain->job->code() : false,
            'Job Status' => $this->complain->job_id ? $this->complain->job->status : false,
            'Order Code' => $this->complain->job_id ? $this->complain->job->partnerOrder->order->code() : false,
            'Complain Applicable For' => $this->complain->accessor->name,
            'Resource' => $this->complain->job_id && $this->complain->job->resource ? $this->complain->job->resource->profile->name : false,
            'OM Name' => $this->complain->job_id && $this->complain->job->crm ? $this->complain->job->crm->name : false,
            'Service Group' => $this->complain->job_id ? ($this->complain->job->category->parent ? $this->complain->job->category->parent->name : false) : false,
            'Service Category' => $this->complain->job_id ? $this->complain->job->category->name : false,
            'Service Name' => !$this->complain->job_id ? false : ($this->complain->job->service_id ? $this->complain->job->service_name : $this->complain->job->jobServices->implode('name', ', ')),
            'Lifetime SLA' => $this->complain->lifetimeInMinute() < $this->complain->preset->complainType->lifetime_sla ? "Within SLA" : "Out of SLA",
            'Customer Name' => $this->complain->customer_id ? $this->complain->customer->name : false,
            'Customer Mobile' => $this->complain->customer_id ? '`' . $this->complain->customer->mobile . '`' : false,
            'New Returning Customer' => ($this->complain->customer && $this->complain->job) ? ($this->complain->job->partnerOrder->order->hasCustomerReturned() ? 'Returning': 'New') : null,
            'Partner Name' => $this->complain->partner_id ? $this->complain->partner->name : false,
            'Complain Assignee' => $this->complain->assigned_to_id ? $this->complain->assignedTo->name : false,
            'Unreachable SMS Sent To SP' => $this->complain->unreachable_sms_sent_to_sp,
            'Unreachable SMS Sent To Customer' => $this->complain->unreachable_sms_sent_to_customer,
            'Unreached Resolve SMS Sent' => $this->complain->unreached_resolve_sms_sent,
            'Follow Up Time' => $this->complain->follow_up_time,
            'IS Satisfied' => !is_null($this->complain->is_satisfied) ? ($this->complain->is_satisfied ? 'Yes' : 'No') : false,
            'Severity' => $this->complain->preset->complainType->name,
            'Created By' => $this->complain->created_by_name,
            'Closed by Assinge Completed' => $this->complain->closed_by_assinge_completed ?: false,
            'Resolved Date' => ($this->complain->resolved_time) ?: false,
            'Resolved Time' => ($this->complain->resolved_time) ?: false,
            'Hours taken to resolve' => ($this->complain->resolved_time) ? round((strtotime($this->complain->resolved_time) - strtotime($this->complain->created_at)) / 3600, 2) : false,
            'Resolved By Name' => $this->complain->resolvedBy ?: false,
            'Resolved Category' => ($this->complain->resolved_category) ?: false,
        ];
    }

    private function getFromComplainReport()
    {
        return [
            'Complain Id' => $this->complainReport->id,
            'Complain Code' => $this->complainReport->complain_code,
            'Complain Status' => $this->complainReport->complain_status,
            'Created Date' =>Carbon::parse($this->complainReport->follow_up_time),
            'Complain' => $this->complainReport->complain,
            'Complain Category' => $this->complainReport->complain_category,
            'Complain Preset' => $this->complainReport->complain_preset,
            'Complain Source' => $this->complainReport->complain_source,
            'Job Code' => $this->complainReport->job_code,
            'Job Status' => $this->complainReport->job_status,
            'Order Code' => $this->complainReport->order_code,
            'Complain Applicable For' => $this->complainReport->complain_applicable_for,
            'Resource' => $this->complainReport->resource,
            'OM Name' => $this->complainReport->om_name,
            'Service Group' => $this->complainReport->service_group,
            'Service Category' => $this->complainReport->service_category,
            'Service Name' => $this->complainReport->service_name,
            'Lifetime SLA' => $this->complainReport->lifetime_sla,
            'Customer Name' => $this->complainReport->customer_name,
            'Customer Mobile' => $this->complainReport->customer_mobile,
            'New Returning Customer' => $this->complainReport->new_returning_customer,
            'Partner Name' => $this->complainReport->partner_name,
            'Complain Assignee' => $this->complainReport->complain_assignee,
            'Unreachable SMS Sent To SP' => $this->complainReport->unreachable_sms_sent_to_sp,
            'Unreachable SMS Sent To Customer' => $this->complainReport->unreachable_sms_sent_to_customer,
            'Unreached Resolve SMS Sent' => $this->complainReport->unreached_resolve_sms_sent,
            'Follow Up Time' => Carbon::parse($this->complainReport->follow_up_time),
            'IS Satisfied' => $this->complainReport->is_satisfied,
            'Severity' => $this->complainReport->severity,
            'Created By' => $this->complainReport->created_by,
            'Closed by Assinge Completed' => $this->complainReport->closed_by_assinge_completed,
            'Resolved Date' => Carbon::parse($this->complainReport->resolved_date),
            'Resolved Time' => Carbon::parse($this->complainReport->resolved_time),
            'Hours taken to resolve' => $this->complainReport->hours_taken_to_resolve,
            'Resolved By Name' => $this->complainReport->resolved_by_name,
            'Resolved Category' => $this->complainReport->complain_applicable_for
        ];
    }

    /** @return array */
    public function getForView()
    {
        $data = $this->get();
        $data['Complain Code'] = $data['Complain Code'] ?: 'N/S';
        $data['Complain Status'] = $data['Complain Status'] ?: 'N/S';
        $data['Created Date'] = $data['Created Date']->format('d M Y');
        $data['Complain'] = $data['Complain'] ?: 'N/S';
        $data['Complain Category'] = $data['Complain Category'] ?: 'N/S';
        $data['Complain Preset'] = $data['Complain Preset'] ?: 'N/S';
        $data['Complain Source'] = $data['Complain Source'] ?: 'N/S';
        $data['Job Code'] = $data['Job Code'] ?: 'N/S';
        $data['Job Status'] = $data['Job Status'] ?: 'N/S';
        $data['Order Code'] = $data['Order Code'] ?: 'N/S';
        $data['Complain Applicable For'] = $data['Complain Applicable For'] ?: 'N/S';
        $data['Resource'] = $data['Resource'] ?: 'N/S';
        $data['OM Name'] = $data['OM Name'] ?: 'N/S';
        $data['Service Group'] = $data['Service Group'] ?: 'N/S';
        $data['Service Category'] = $data['Service Category'] ?: 'N/S';
        $data['Service Name'] = $data['Service Name'] ?: 'N/S';
        $data['Lifetime SLA'] = $data['Lifetime SLA'] ?: 'N/S';
        $data['Customer Name'] = $data['Customer Name'] ?: 'N/S';
        $data['Customer Mobile'] = $data['Customer Mobile'] ?: 'N/S';
        $data['New Returning Customer'] = $data['New Returning Customer'] ? : 'N/S';
        $data['Partner Name'] = $data['Partner Name'] ?: 'N/S';
        $data['Complain Assignee'] = $data['Complain Assignee'] ?: 'N/S';
        $data['Unreachable SMS Sent To SP'] = $data['Unreachable SMS Sent To SP'] ? 'Yes' : 'No';
        $data['Unreachable SMS Sent To Customer'] = $data['Unreachable SMS Sent To Customer'] ? 'Yes' : 'No';
        $data['Unreached Resolve SMS Sent'] = $data['Unreached Resolve SMS Sent'] ? 'Yes' : 'No';
        $data['Follow Up Time'] = $data['Follow Up Time']->format('d M Y');
        $data['IS Satisfied'] = $data['IS Satisfied'] ?: 'N/S';
        $data['Severity'] = $data['Severity'] ?: 'N/S';
        $data['Created By'] = $data['Created By'] ?: 'N/S';
        $data['Closed by Assinge Completed'] = $data['Closed by Assinge Completed'] ? $data['Closed by Assinge Completed'] : 'N/S';
        $data['Resolved Date'] = $data['Resolved Date'] ? $data['Resolved Date']->format('d M Y') : 'N/S';
        $data['Resolved Time'] = $data['Resolved Time'] ? $data['Resolved Time']->format('g:i:s a') : 'N/S';
        $data['Hours taken to resolve'] = $data['Hours taken to resolve'] ?: 'N/S';
        $data['Resolved By Name'] = $data['Resolved By Name'] ?: 'N/S';
        $data['Resolved Category'] = $data['Resolved Category'] ?: 'N/S';
        return $data;
    }

    public function getForTable()
    {
        $data = $this->get();
        return [
            'complain_code' => $data['Complain Code'],
            'complain_status' => $data['Complain Status'],
            'created_at' => $data['Created Date'],
            'complain' => $data['Complain'],
            'complain_category' => $data['Complain Category'],
            'complain_preset' => $data['Complain Preset'],
            'complain _source' => $data['Complain Source'],
            'job_code' => $data['Job Code'],
            'job_status' => $data['Job Status'],
            'order_code' => $data['Order Code'],
            'complain_applicable_for' => $data['Complain Applicable For'],
            'resource' => $data['Resource'],
            'om_name' => $data['OM Name'],
            'service_group' => $data['Service Group'],
            'service_category' => $data['Service Category'],
            'service_name' => $data['Service Name'],
            'lifetime_sla' => $data['Lifetime SLA'],
            'customer_name' => $data['Customer Name'],
            'customer_mobile' => $data['Customer Mobile'],
            'new_returning_customer' => $data['New Returning Customer'],
            'partner_name' => $data['Partner Name'],
            'complain_assignee' => $data['Complain Assignee'],
            'unreachable_sms_sent_to_sp' => $data['Unreachable SMS Sent To SP'],
            'unreachable_sms_sent_to_customer' => $data['Unreachable SMS Sent To Customer'],
            'unreached_resolve_sms_sent' => $data['Unreached Resolve SMS Sent'],
            'follow_up_time' => $data['Follow Up Time'],
            'is_satisfied' => $data['IS Satisfied'],
            'severity' => $data['Severity'],
            'created_by' => $data['Created By'],
            'closed_by_assinge_completed' => $data['Closed by Assinge Completed'],
            'resolved_date' => $data['Resolved Date'],
            'resolved_time' => $data['Resolved Time'],
            'hours_taken_to_resolve' => $data['Hours taken to resolve'],
            'resolved_by_name' => $data['Resolved By Name'],
            'resolved_category' => $data['Resolved Category']
        ];
    }
}




































