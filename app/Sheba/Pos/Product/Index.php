<?php namespace Sheba\Pos\Product;


use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;

class Index
{
    private $posServiceRepository;
    private $partnerId;
    private $publishedForShop;
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


    public function fetch()
    {
        return $this->posServiceRepository->where('publication_status', $this->isPublished)
            ->where('is_published_for_shop', $this->isPublishedForShop)
            ->where('partner_id', $this->partnerId)
            ->whereRaw("stock > 0")
            ->select(['id', 'name', 'thumb', 'price', 'unit', 'stock'])
            ->get();
    }
}