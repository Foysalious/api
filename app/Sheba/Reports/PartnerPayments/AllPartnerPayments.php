<?php namespace Sheba\Reports\PartnerPayments;

use App\Http\Requests\SPPaymentReportRequest;
use App\Models\Partner;
use App\Models\PartnerOrder;

class AllPartnerPayments extends PartnerPayments
{
    /**
     * @return array
     */
    public function get()
    {
        $partners = Partner::all();
        $data = [];
        foreach ($partners as $partner) {
            /** @var Partner $partner */
            $partner_orders = $partner->orders()->with('jobs');

            if ($this->request->payable_session != 'lifetime') {
                $partner_orders = $this->notLifetimeQuery($partner_orders, $this->session, 'closed_at');
            } else {
                $partner_orders = $partner_orders->whereNotNull('closed_at');
            }
            $partner_orders = $partner_orders->get()->map(function (PartnerOrder $partner_order) {
                return $partner_order->calculate(true);
            });
            $data[$partner->name] = $partner_orders;
        }
        return $data;
    }
}