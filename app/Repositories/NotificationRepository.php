<?php

namespace App\Repositories;

use App\Models\Partner;
use App\Models\PartnerOrder;

class NotificationRepository
{
    private $order;
    private $sender_id;
    private $sender_type;

//    public function __construct($order)
//    {
//        $this->order = $order;
//        $this->send();
//    }

    public function send($order)
    {
        $this->order = $order;
        if ($this->order->sales_channel == 'Web') {
            $this->sender_id = $this->order->customer_id;
            $this->sender_type = 'customer';

            $this->sendNotificationToBackEnd();
        } else {
            $this->sender_id = $this->order->created_by;
            $this->sender_type = 'user';

            $this->sendNotificationToCRM($this->order->jobs); //REMOVE
        }
        $this->sendNotificationToPartner($this->order->partner_orders);
    }

    private function sendNotificationToCRM($jobs)
    {
        foreach ($jobs as $job) {
            notify()->user($job->crm_id)->sender($this->sender_id, $this->sender_type)->send([
                'title' => 'You have been assigned to a new job',
                'link' => env('SHEBA_BACKEND_URL') . '/job/' . $job->id,
                'type' => notificationType('Info')
            ]);
        }
    }

    private function sendNotificationToBackEnd()
    {
        notify()->departments([5, 7])->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'New Order Placed From Front End',
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    private function sendNotificationToPartner($partner_orders)
    {
        foreach ($partner_orders as $partner_order) {
            $partner = Partner::find($partner_order->partner_id);
            notify()->partner($partner->id)->sender($this->sender_id, $this->sender_type)->send([
                'title' => 'New Order Placed',
                'link' => env('SHEBA_PARTNER_END_URL') . '/' . $partner->sub_domain . '/order/' . $partner_order->id,
                'type' => notificationType('Info')
            ]);
        }
    }

    public function forOnlinePayment($partner_order, $amount)
    {
        $partner_order = ($partner_order instanceof PartnerOrder) ? $partner_order : PartnerOrder::find($partner_order);
        $this->order =$partner_order->order;
        $this->order = $partner_order->order;
        $this->sender_id = $this->order->customer_id;
        $this->sender_type = 'customer';
        $this->_sendPaymentNotificationToCM($partner_order, $amount);
    }

    private function _sendPaymentNotificationToCM($partner_order, $amount)
    {
        notify()->user($partner_order->jobs[0]->crm_id)->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'An online payment of ' . $amount . ' has been completed to this order ' . $this->order->code(),
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    public function forAffiliateRegistration($affiliate)
    {
        notify()->departments([3, 8])->send([
            'title' => 'New Affiliate Registration from ' . $affiliate->profile->mobile,
            'link' => env('SHEBA_BACKEND_URL') . '/affiliate/' . $affiliate->id,
            'type' => notificationType('Info')
        ]);
    }

    public function forAffiliation($affiliate, $affiliation)
    {
        notify()->department(7)->send([
            'title' => 'New Affiliation Arrived from ' . $affiliate->profile->mobile,
            'link' => env('SHEBA_BACKEND_URL') . '/affiliation/' . $affiliation->id,
            'type' => notificationType('Info')
        ]);
    }

}