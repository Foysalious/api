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
        foreach ($this->favoriteServices as $service) {
            if (!$service->isMarketPlacePublished()) return 0;
            if ($service->variable_type != $service->pivot->variable_type) return 0;
        }
        return 1;
    }
}