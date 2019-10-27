<?php namespace Sheba\Repositories\Business;


use App\Models\BidItem;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\BidItemRepositoryInterface;

class BidItemRepository extends BaseRepository implements BidItemRepositoryInterface
{
    public function __construct(BidItem $bid_item)
    {
        parent::__construct();
        $this->setModel($bid_item);
    }
}