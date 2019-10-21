<?php namespace App\Sheba\Business\Bid;


use App\Models\Bid;
use App\Sheba\Repositories\Business\BidRepository;

class Updater
{
    private $bidRepository;
    private $isFavourite;
    private $bidData;
    
    public function __construct(BidRepository $bid_repository)
    {
        $this->bidRepository = $bid_repository;
    }

    public function setIsFavourite($is_favourite)
    {
        $this->isFavourite = $is_favourite;
        return $this;
    }

    public function updateFavourite(Bid $bid)
    {
        $this->bidData = [
            'is_favourite' => $this->isFavourite ? (int)$this->isFavourite : 0,
        ];
        $this->bidRepository->update($bid, $this->bidData);
    }
}