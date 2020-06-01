<?php namespace Sheba\Order\Policy;


use Illuminate\Database\Eloquent\Collection;

class FavoriteService extends Orderable
{
    /** @var Collection */
    private $favoriteServices;

    /**
     * @param Collection $favoriteServices
     * @return FavoriteService
     */
    public function setFavoriteServices($favoriteServices)
    {
        $this->favoriteServices = $favoriteServices;
        return $this;
    }

    public function canOrder()
    {
     dd($this->favoriteServices);
    }
}