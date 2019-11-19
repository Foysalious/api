<?php namespace Sheba\OrderRequest\Repositories;

use App\Models\PartnerOrderRequest;
use Sheba\OrderRequest\Repositories\Interfaces\OrderRequestRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class OrderRequestRepository extends BaseRepository implements OrderRequestRepositoryInterface
{
    public function __construct(PartnerOrderRequest $partner_order_request)
    {
        parent::__construct();
        $this->setModel($partner_order_request);
    }

    public function load()
    {
        return $this->model->with(
            'partnerOrder.jobs.jobServices',
            'partnerOrder.jobs.discounts',
            'partnerOrder.jobs.usedMaterials',
            'partnerOrder.jobs.partnerChangeLog',
            'partnerOrder.jobs.review',
            'partnerOrder.jobs.complains',
            'partnerOrder.jobs.cancelLog',
            'partnerOrder.jobs.category',
            'partnerOrder.order.subscription',
            'partnerOrder'
        );
    }
}
