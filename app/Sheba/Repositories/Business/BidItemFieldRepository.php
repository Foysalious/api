<?php namespace Sheba\Repositories\Business;

use App\Models\BidItemField;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;

class BidItemFieldRepository extends BaseRepository implements BidItemFieldRepositoryInterface
{
    public function __construct(BidItemField $bid_item_field)
    {
        parent::__construct();
        $this->setModel($bid_item_field);
    }
}