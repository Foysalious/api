<?php namespace Sheba\Business\Bid;

use App\Models\Bid;
use App\Sheba\Repositories\Business\BidRepository;
use Carbon\Carbon;

class Creator
{
    private $bidRepository;

    public function __construct(BidRepository $bid_repository)
    {
        $this->bidRepository = $bid_repository;
    }
}