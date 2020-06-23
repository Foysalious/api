<?php namespace App\Transformers;

use App\Models\PosOrderLog;
use League\Fractal\TransformerAbstract;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderItemRepository;

class PosOrderReturnedTransformer extends TransformerAbstract
{
    /** @var PosServiceRepositoryInterface $serviceRepo */
    private $serviceRepo, $itemRepo;

    public function __construct()
    {
        $this->serviceRepo = app(PosServiceRepositoryInterface::class);
        $this->itemRepo    = app(PosOrderItemRepository::class);
    }

    public function transform(PosOrderLog $order_log)
    {
        $orders  = [];
        $details = $order_log->details;
        foreach ($details->items->changes as $key => $item) {
            $service          = $this->itemRepo->getModel()->find($key);
            $orders['item'][] = [
                'id'              => $service->service ? $service->service->id : null,
                'item_id'         => $service->id,
                'name'            => $service->service_name,
                'app_thumb'       => $service->service ? $service->service->app_thumb : null,
                'unit_price'      => (double)$item->unit_price,
                'old_quantity'    => $item->qty->old,
                'new_quantity'    => $item->qty->new,
                'backed_quantity' => $item->qty->old - $item->qty->new
            ];
        }
        $orders['old_total_sale']        = (double)$details->items->total_sale;
        $orders['old_vat_amount']        = (double)$details->items->vat_amount;
        $orders['total_returned_amount'] = (double)$details->items->returned_amount;
        return $orders;
    }
}
