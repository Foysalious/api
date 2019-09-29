<?php namespace Sheba\Repositories\Business;

use App\Models\PurchaseRequestItemField;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\PurchaseRequestItemFieldRepositoryInterface;

class PurchaseRequestItemFieldRepository extends BaseRepository implements PurchaseRequestItemFieldRepositoryInterface
{
    public function __construct(PurchaseRequestItemField $purchase_request_item_field)
    {
        parent::__construct();
        $this->setModel($purchase_request_item_field);
    }
}