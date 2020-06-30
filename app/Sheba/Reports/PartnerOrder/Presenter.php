<?php namespace Sheba\Reports\PartnerOrder;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderReport;
use Carbon\Carbon;
use InvalidArgumentException;
use Sheba\Logs\OrderLogs;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var PartnerOrderReport */
    private $partnerOrderReport;

    protected $fields = [
        'order_code' => 'Order Code',
        'order_media' => 'Order Media',
        'order_channel' => 'Order Channel',
        'order_portal' => 'Order Portal',
        'order_unique_id' => 'Order Unique ID',
        'order_first_created' => 'Order First Created',
        'created_date' => 'Created Date',
        'request_created_date' => 'Request Created Date',
        'customer_id' => 'Customer ID',
        'customer_name' => 'Customer Name',
        'customer_mobile' => 'Customer Mobile',
        'customer_total_order' => 'Customer Total Order',
        'customer_registration_date' => 'Customer Registration Date',
        'location' => 'Location',
        'delivery_name' => 'Delivery Name',
        'delivery_mobile' => 'Delivery Mobile',
        'delivery_address' => 'Delivery Address',
        'is_vip' => 'Is Vip',
        'sp_id' => 'SP Id',
        'sp_name' => 'SP Name',
        'sp_mobile' => 'SP Mobile',
        'sp_type' => 'SP Type',
        'resource_id' => 'Resource Id',
        'resource_name' => 'Resource Name',
        'is_sp_changed' => 'Is SP Changed',
        'status' => 'Status',
        'job_status' => 'Job Status',
        'serving_life_time' => 'Serving Life Time',
        'cancelled_date' => 'Cancelled Date',
        'cancel_reason' => 'Cancel Reason',
        'closed_date' => 'Closed Date',
        'closed_and_paid_date' => 'Closed and Paid Date',
        'payment_status' => 'Payment Status',
        'promo' => 'Promo',
        'promo_tags' => 'Promo Tags',
        'reference_id' => 'Reference ID',
        'agent_tags' => 'Agent Tags',
        'csat' => 'CSAT',
        'user_acquisition_or_retention' => 'User Acquisition/Retention',
        'user_complaint' => 'User Complaint',
        'sp_complaint' => 'SP Complaint',
        'total_complaint' => 'Total Complaint',
        'created_by' => 'Created By',
        'order_updated_at' => 'Updated At',
        'om' => 'OM',
        'additional_info' => 'Additional Info',
        'quantity' => 'Quantity',
        'schedule_date' => 'Schedule Date',
        'schedule_time' => 'Schedule Time',
        'csat_date' => 'CSAT Date',
        'csat_by' => 'CSAT BY',
        'reschedule_counter' => 'Reschedule Counter',
        'schedule_due_counter' => 'Schedule Due Counter',
        'price_change_counter' => 'Price Change Counter',
        'accept_date' => 'Accept Date',
        'accepted_by' => 'Accepted By',
        'accepted_from' => 'Accepted From',
        'declined_date' => 'Declined Date',
        'declined_by' => 'Declined By',
        'declined_from' => 'Declined From',
        'served_by' => 'Served By',
        'served_from' => 'Served From',
        'cancel_by' => 'Cancel By',
        'cancel_from' => 'Cancel From',
        'cancel_requested_by' => 'Cancel Requested By',
        'cancel_reason_details' => 'Cancel Reason Details',
        'complain_id' => 'Complain ID',
        'csat_remarks' => 'CSAT Remarks',
        'status_changes' => 'Status Changes',
        'master_category_id' => 'Master Category ID',
        'master_category_name' => 'Master Category Name',
        'service_category_id' => 'Service Category ID',
        'service_category_name' => 'Service Category Name',
        'service_id' => 'Service ID',
        'services' => 'Services',
        'gmv_service' => 'GMV (Service)',
        'gmv_material' => 'GMV (Material)',
        'gmv_delivery' => 'GMV (Delivery)',
        'gmv' => 'GMV',
        'discount' => 'Discount',
        'discount_sheba' => 'Discount (Sheba)',
        'discount_partner' => 'Discount (Partner)',
        'rounding_cut_off' => 'Rounding Cut Off',
        'billed_amount' => 'Billed Amount',
        'service_charge' => 'Service Charge',
        'revenue' => 'Revenue',
        'sp_cost_service' => 'SP Cost (Service)',
        'sp_cost_additional' => 'SP Cost (Additional)',
        'sp_cost_delivery' => 'SP Cost (Delivery)',
        'sp_cost' => 'SP Cost',
        'collected_sheba' => 'Collected (Sheba)',
        'collected_sp' => 'Collected (SP)',
        'collection' => 'Collection',
        'contained_by_sheba' => 'Contained By Sheba',
        'contained_by_sp' => 'Contained By SP',
        'due' => 'Due',
        'profit' => 'Profit',
        'sp_payable' => 'SP Payable',
        'sheba_receivable' => 'Sheba Receivable',
        'collected_by_finance' => 'Collected by finance',
        'revenue_percentage' => 'Revenue %',
        'service_charge_percentage' => 'Service Charge %'
    ];

    private $viewData;

    public function __construct()
    {
        $this->initializeViewData();
    }

    private function initializeViewData()
    {
        foreach ($this->fields as $field) {
            $this->viewData[$field] = '';
        }
    }

    /**
     * @param PartnerOrder $partner_order
     * @return $this
     */
    public function setPartnerOrder(PartnerOrder $partner_order)
    {
        $this->partnerOrder = $partner_order;
        return $this;
    }

    /**
     * @param PartnerOrderReport $partner_order_report
     * @return $this
     */
    public function setPartnerOrderReport(PartnerOrderReport $partner_order_report)
    {
        $this->partnerOrderReport = $partner_order_report;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->calculate();
        return $this->viewData;
    }

    private function calculate()
    {
        if ($this->partnerOrderReport) {
            $this->getFromPartnerOrderReport();
        } elseif ($this->partnerOrder) {
            $this->getFromPartnerOrder();
        } else {
            throw new InvalidArgumentException('Instance of PartnerOrder or PartnerOrderReport must be defined.');
        }
    }

    public function getForView()
    {
        $this->calculate();
        $data = $this->viewData;
        $data['Created Date'] = $data['Created Date'] ? $data['Created Date']->format('d M Y H:i') : "N/F";
        $data['Order First Created'] = $data['Order First Created'] ? $data['Order First Created']->format('d M Y H:i') : "N/F";
        $data['Cancelled Date'] = $data['Cancelled Date'] ? $data['Cancelled Date']->format('d M Y H:i') : "N/S";
        $data['Closed Date'] = $data['Closed Date'] ? $data['Closed Date']->format('d M Y H:i') : "N/S";
        $data['Closed and Paid Date'] = $data['Closed and Paid Date'] ? $data['Closed and Paid Date']->format('d M Y H:i') : "N/S";
        $data['Customer Mobile'] = '`' . $data['Customer Mobile'] . '`';
        $data['Customer Name'] = $data['Customer Name'] ?: "N/S";
        $data['Order Portal'] = $data['Order Portal'] ?: "N/S";
        $data['Delivery Address'] = $data['Delivery Address'] ?: "N/A";
        $data['Resource Id'] = $data['Resource Id'] ?: "N/A";
        $data['Resource Name'] = $data['Resource Name'] ?: "N/A";
        $data['Cancel Reason'] = $data['Cancel Reason'] ?: "N/A";
        $data['Promo'] = $data['Promo'] ?: "N/A";
        $data['Reference ID'] = $data['Reference ID'] ?: "N/S";
        $data['CSAT'] = $data['CSAT'] ?: "N/S";
        $data['Delivery Mobile'] = $data['Delivery Mobile'] ? '`' . $data['Delivery Mobile'] . '`' : "N/S";
        $data['Customer Registration Date'] = $data['Customer Registration Date'] ? $data['Customer Registration Date']->format('d M Y H:i') : "N/F";
        $data['Updated At'] = $data['Updated At'] ? $data['Updated At']->format('d M Y H:i') : "N/F";
        $data['Is SP Changed'] = $data['Is SP Changed'] ? "Yes" : "No";
        $data['Quantity'] = $data['Quantity'] ?: "N/S";
        $data['Schedule Date'] = $data['Schedule Date'] ? $data['Schedule Date']->format('d M Y') : "N/S";
        $data['CSAT Date'] = $data['CSAT Date'] ? $data['CSAT Date']->format('d M Y h:i A') : "N/S";
        $data['Accept Date'] = $data['Accept Date'] ? $data['Accept Date']->format('d M Y h:i A') : "N/S";
        $data['Accepted By'] = $data['Accepted By'] ?: "N/S";
        $data['Accepted From'] = $data['Accepted From'] ?: "N/S";
        $data['Declined Date'] = $data['Declined Date'] ? $data['Declined Date']->format('d M Y h:i A') : "N/S";
        $data['Declined By'] = $data['Declined By'] ?: "N/S";
        $data['Declined From'] = $data['Declined From'] ?: "N/S";
        $data['Served By'] = $data['Served By'] ?: "N/S";
        $data['Served From'] = $data['Served From'] ?: "N/S";
        $data['Cancel By'] = $data['Cancel By'] ?: "N/S";
        $data['Cancel From'] = $data['Cancel From'] ?: "N/S";
        $data['Cancel Requested By'] = $data['Cancel Requested By'] ?: "N/S";
        $data['Cancel Reason Details'] = $data['Cancel Reason Details'] ?: "N/S";
        $data['CSAT Remarks'] = $data['CSAT Remarks'] ?: "N/S";
        $data['Revenue %'] = $data['Revenue %'] . "%";
        $data['Service Charge %'] = $data['Service Charge %'] . "%";
        $data['Info Call Id'] = $data['Info Call Id'] ?: 'N/S';
        $this->viewData = $data;
        return $this->viewData;
    }

    private function getFromPartnerOrder()
    {
        $partner_order = $this->partnerOrder->calculate(true);
        $partner_order_data = [
            'Order Code' => $partner_order->code(),
            'Order Media' => $partner_order->order->sales_channel,
            'Order Channel' => $partner_order->order->shortChannel(),
            'Order Portal' => $partner_order->order->portal_name,
            'Order Unique ID' => $partner_order->order->id,
            'Order First Created' => $partner_order->order->created_at,
            'Created Date' => $partner_order->created_at,
            'Customer ID' => $partner_order->order->customer->id,
            'Customer Name' => $partner_order->order->customer->name,
            'Customer Mobile' => $partner_order->order->customer->mobile,
            'Customer Total Order' => $partner_order->order->customer->orders->count(),
            'Customer Registration Date' => $partner_order->order->customer->created_at,
            'Location' => $partner_order->order->location->name,
            'Delivery Name' => $partner_order->order->delivery_name,
            'Delivery Mobile' => $partner_order->order->delivery_mobile,
            'Delivery Address' => $partner_order->order->deliveryAddress->address,
            'Is Vip' => $partner_order->order->customer->is_vip,
            'SP Id' => $partner_order->partner_id,
            'SP Name' => $partner_order->partner ? $partner_order->partner->name : 'N/S',
            'SP Mobile' => $partner_order->partner ? $partner_order->partner->mobile : 'N/S',
            'SP Type' => $partner_order->partner ? $partner_order->partner->subscription->name : 'N/S',
            'Info Call Id' => $partner_order->order->info_call_id,
            'Request Created Date' => $this->getOrderRequestDate($partner_order)
        ];
        $this->viewData = array_merge($this->viewData, $partner_order_data);

        /** @var Job $job */
        $job = $partner_order->lastJob();
        if (!$job) return;

        $status_change_info = $job->statusChangeLogs->pluckMultiple(['created_by_name', 'created_at', 'portal_name'], 'to_status')->toArray();
        $accepted_status_change = $job->statusChangeLogs->where('to_status', 'Accepted')->first();
        $job_data = [
            'Resource Id' => $job->resource ? $job->resource->id : null,
            'Resource Name' => $job->resource ? $job->resource->profile->name : null,
            'Is SP Changed' => (new OrderLogs($partner_order->order))->partnerChangeLogs()->count(),
            'Status' => $partner_order->status,
            'Job Status' => $partner_order->active_job->status . (($partner_order->active_job->status == "Cancelled" && $partner_order->active_job->partnerChangeLog) ? "(Partner Changed)" : ""),
            'Serving Life Time' => $partner_order->lifetime(),
            'Cancelled Date' => ($partner_order->status == "Cancelled") && ($partner_order->cancelled_at != null) ? $partner_order->cancelled_at : null,
            'Cancel Reason' => $partner_order->cancelled_at ? ($job->partnerChangeLog ? $job->partnerChangeLog->cancel_reason : ($job->cancelLog ? $job->cancelLog->cancel_reason : null)) : null,
            'Closed Date' => $partner_order->closed_at,
            'Closed and Paid Date' => $partner_order->closed_and_paid_at,
            'Payment Status' => $partner_order->paymentStatus,
            'Promo' => $partner_order->order->voucher ? $partner_order->order->voucher->code : null,
            'Promo Tags' => $partner_order->order->voucher ? (!$partner_order->order->voucher->tag_names->isEmpty() ? $partner_order->order->voucher->tag_names->implode(',') : 'N/A') : "N/S",
            'Reference ID' => $partner_order->order->affiliation_id,
            'Agent Tags' => !$partner_order->order->affiliation_id ? 'N/S' : ($partner_order->order->affiliation->affiliate->tag_names->isEmpty() ? implode(', ', $partner_order->order->affiliation->affiliate->tag_names->toArray()) : 'N/A'),
            'CSAT' => $job->review ? $job->review->rating : null,
            'User Acquisition/Retention' => $partner_order->order->hasCustomerReturned() ? "Returning" : "New",
            'User Complaint' => $job->complains->where('accessor_id', 1)->count(),
            'SP Complaint' => $job->complains->where('accessor_id', 2)->count(),
            'Total Complaint' => $job->complains->count(),
            'Created By' => $partner_order->created_by_name,
            'Updated At' => $partner_order->updated_at,
            'OM' => $partner_order->getAllCmNames()->implode(', '),
            'Additional Info' => $job->job_additional_info,
            'Quantity' => $job->service_quantity,
            'Schedule Date' => $job->schedule_date ? new Carbon($job->schedule_date) : null,
            'Schedule Time' => $job->preferred_time,
            'CSAT Date' => $job->review ? $job->review->created_at : null,
            'CSAT BY' => $job->review ? ($job->review->created_by_name ?: "Customer - " . $job->partnerOrder->order->customer->profile->name) : 'N/S',
            'Reschedule Counter' => $job->rescheduleCounter(),
            'Schedule Due Counter' => $job->scheduleDueLog->isEmpty() ? 0 : $job->scheduleDueLog->count(),
            'Price Change Counter' => $job->priceChangeCounter(),
            'Accept Date' => !empty($accepted_status_change) ? $accepted_status_change->created_at : null,
            'Accepted By' => !empty($accepted_status_change) ? $accepted_status_change->created_by_name : null,
            'Accepted From' => !empty($accepted_status_change) ? $accepted_status_change->portal_name : null,
            'Declined Date' => isset($status_change_info['Declined']) ? $status_change_info['Declined']['created_at'] : null,
            'Declined By' => isset($status_change_info['Declined']) ? $status_change_info['Declined']['created_by_name'] : null,
            'Declined From' => isset($status_change_info['Declined']['portal_name']) ? $status_change_info['Declined']['portal_name'] : null,
            'Served By' => isset($status_change_info['Served']) ? $status_change_info['Served']['created_by_name'] : null,
            'Served From' => isset($status_change_info['Served']['portal_name']) ? $status_change_info['Served']['portal_name'] : null,
            'Cancel By' => isset($status_change_info['Cancelled']) ? $status_change_info['Cancelled']['created_by_name'] : null,
            'Cancel From' => isset($status_change_info['Cancelled']['portal_name']) ? $status_change_info['Cancelled']['portal_name'] : null,
            'Cancel Requested By' => $partner_order->status == 'Cancelled' ? $job->lastCancelRequestBy() : null,
            'Cancel Reason Details' => $partner_order->cancelled_at ? ($job->cancelLog ? $job->cancelLog->cancel_reason_details : null) : null,
            'Complain ID' => $job->complains->pluck('id')->implode(','),
            'CSAT Remarks' => $job->review ? $job->review->review : null,
            'Status Changes' => 'Pending,' . $partner_order->statusChangeLogs->pluck('to_status')->implode(','),
            'Master Category ID' => $job->masterCategory()->id,
            'Master Category Name' => $job->masterCategory()->name,
            'Service Category ID' => $job->category->id,
            'Service Category Name' => $job->category->name,
            'Service ID' => $job->serviceIds(),
            'Services' => $job->service_id ? $job->service->name : $job->jobServices->pluck('name')->implode(','),
            'GMV (Service)' => $partner_order->totalServicePrice,
            'GMV (Material)' => $partner_order->totalMaterialPrice,
            'GMV (Delivery)' => $partner_order->deliveryCharge,
            'GMV' => $partner_order->jobPrices,
            'Discount' => $partner_order->totalDiscount,
            'Discount (Sheba)' => $partner_order->totalShebaDiscount,
            'Discount (Partner)' => $partner_order->totalPartnerDiscount,
            'Rounding Cut Off' => $partner_order->roundingCutOff,
            'Billed Amount' => $partner_order->grossAmount,
            'Service Charge' => $partner_order->serviceCharge,
            'Revenue' => $partner_order->revenue,
            'SP Cost (Service)' => $partner_order->totalServiceCost,
            'SP Cost (Additional)' => $partner_order->totalMaterialCost,
            'SP Cost (Delivery)' => $partner_order->deliveryCost,
            'SP Cost' => $partner_order->totalCost,
            'Collected (Sheba)' => $partner_order->total_sheba_collection,
            'Collected (SP)' => $partner_order->total_sp_collection,
            'Collection' => $partner_order->paid,
            'Contained By Sheba' => $partner_order->sheba_collection,
            'Contained By SP' => $partner_order->partner_collection,
            'Due' => $partner_order->due,
            'Profit' => $partner_order->profit,
            'SP Payable' => $partner_order->spPayable,
            'Sheba Receivable' => $partner_order->shebaReceivable,
            'Collected by finance' => $partner_order->finance_collection,
            'Revenue %' => $partner_order->revenuePercent,
            'Service Charge %' => $partner_order->serviceChargePercent
        ];
        $this->viewData = array_merge($this->viewData, $job_data);
    }

    private function getOrderRequestDate(PartnerOrder $partner_order)
    {
        if (!$partner_order->partner) return null;
        $order_request = $partner_order->partnerOrderRequests->where('partner_id', $partner_order->partner_id)->first();
        if (!$order_request) return null;
        return $order_request->created_date;
    }

    private function getFromPartnerOrderReport()
    {
        foreach ($this->fields as $table_field => $view_field) {
            $this->viewData[$view_field] = $this->partnerOrderReport->$table_field;
        }
    }

    public function getForTable()
    {
        $this->calculate();

        $table_data = [];
        foreach ($this->fields as $table_field => $view_field) {
            $table_data[$table_field] = $this->viewData[$view_field];
        }

        return $table_data;
    }
}
