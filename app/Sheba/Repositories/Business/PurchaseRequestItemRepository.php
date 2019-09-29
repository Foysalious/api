<?php namespace Sheba\Repositories\Business;

use App\Models\PurchaseRequestItem;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\PurchaseRequestItemRepositoryInterface;

class PurchaseRequestItemRepository extends BaseRepository implements PurchaseRequestItemRepositoryInterface
{
    public function __construct(PurchaseRequestItem $purchase_request_item)
    {
        parent::__construct();
        $this->setModel($purchase_request_item);
    }
}