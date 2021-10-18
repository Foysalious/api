<?php namespace Sheba\Pos\Product;

use Illuminate\Support\Facades\DB;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;

class Index
{
    private $posServiceRepository;
    private $partnerId;
    private $partnerSlug;
    private $isPublishedForShop;
    private $isPublished;

    public function __construct(PosServiceRepositoryInterface $pos_service_repository)
    {
        $this->posServiceRepository = $pos_service_repository;
        $this->isPublishedForShop = 0;
        $this->isPublished = 1;
    }


    /**
     * @param bool $publishedForShop
     * @return $this
     */
    public function setIsPublishedForShop(bool $publishedForShop)
    {
        $this->isPublishedForShop = +$publishedForShop;
        return $this;
    }


    /**
     * @param bool $published
     * @return $this
     */
    public function setIsPublished(bool $published)
    {
        $this->isPublished = +$published;
        return $this;
    }

    /**
     * @param int $partnerId
     * @return Index
     */
    public function setPartnerId(int $partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function setPartnerSlug($slug)
    {
        $this->partnerSlug = $slug;
        return $this;
    }


    public function getAvailableProducts()
    {
        $query = $this->posServiceRepository
            ->where('publication_status', $this->isPublished)
            ->where('is_published_for_shop', $this->isPublishedForShop)
            ->select('id', 'partner_id', 'name', 'thumb', 'app_thumb', 'price', 'unit', 'stock', 'pos_category_id', 'vat_percentage','weight','weight_unit','is_emi_available');

        if ($this->partnerId) $query = $query->where('partner_id', $this->partnerId);
        else {
            $query = $query->whereHas('partner', function ($q) {
                $q->where('sub_domain', $this->partnerSlug);
            });
        }
        return $query->get();
    }
}
