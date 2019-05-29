<?php namespace Sheba\Reports\OfferAnalysis;

use App\Models\OfferShowcase;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

class Getter extends ReportData
{
    /** @var Presenter */
    private $presenter;

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }
    /**
     * @param Request $request
     * @return Collection
     */
    public function get(Request $request)
    {
        $target_types = ["App\\Models\\Voucher", "App\\Models\\Category", "App\\Models\\Service",
            "App\\Models\\CategoryGroup", "App\\Models\\ServiceGroup"];
        $all_orders = collect();
        $offers = OfferShowcase::whereIn('target_type', $target_types)
            ->get()->map(function (OfferShowcase $offer) use(&$all_orders) {
            $orders = Order::select('orders.id', 'orders.sales_channel')
                ->distinct('orders.id')
                ->join('partner_orders', 'partner_orders.order_id', '=', 'orders.id')
                ->join('jobs', 'partner_orders.id', '=', 'jobs.partner_order_id')
                ->join('job_service', 'job_service.job_id', '=', 'jobs.id')
                ->whereBetween('orders.created_at', [$offer->start_date, $offer->end_date]);
            if($offer->isVoucher()) {
                $orders = $orders->where('orders.voucher_id', $offer->target_id);
            } else if ($offer->isCategory()) {
                $orders = $orders->where('jobs.category_id', $offer->target_id);
            } else if ($offer->isService()) {
                $orders = $orders->where('job_service.service_id', $offer->target_id);
            } else if ($offer->isCategoryGroup()) {
                $orders = $orders->whereIn('jobs.category_id', function($q) use ($offer) {
                    $q->select('category_id')->from('category_group_category')
                        ->where('category_group_id', $offer->target_id);
                });
            } else if ($offer->isServiceGroup()) {
                $orders = $orders->whereIn('job_service.service_id', function($q) use ($offer) {
                    $q->select('service_id')->from('service_group_service')
                        ->where('service_group_id', $offer->target_id);
                });
            }
            $all_orders = $all_orders->merge($orders->get()->map(function ($order) use($offer) {
                return [
                    'offer_id' => $offer->id,
                    'order_code' => $order->code()
                ];
            }));

            $offer->order_count = $orders->count();
            return $this->presenter->setOffer($offer)->getForView();
        });
        return [
            'offer_data' => $offers->toArray(),
            'order_data' => $all_orders->toArray()
        ];
    }
}
