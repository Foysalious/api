<?php namespace Sheba\Repositories\Business;

use App\Models\PurchaseRequest;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\PurchaseRequestRepositoryInterface;

class PurchaseRequestRepository extends BaseRepository implements PurchaseRequestRepositoryInterface
{
    public function __construct(PurchaseRequest $purchase_request)
    {
        parent::__construct();
        $this->setModel($purchase_request);
    }
}