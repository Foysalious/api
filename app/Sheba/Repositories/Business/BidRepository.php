<?php namespace App\Sheba\Repositories\Business;

use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use Sheba\Repositories\BaseRepository;
use App\Models\Bid;

class BidRepository extends BaseRepository implements BidRepositoryInterface
{
    public function __construct(Bid $bid)
    {
        parent::__construct();
        $this->setModel($bid);
    }
}