<?php namespace Sheba\Pos\Repositories;


use Sheba\Pos\Repositories\Interfaces\PosServiceImageGalleyRepositoryInterface;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\Repositories\BaseRepository;

class PosServiceImageGalleryRepository extends BaseRepository implements PosServiceImageGalleyRepositoryInterface
{
    public function __construct(PartnerPosServiceImageGallery $partnerPosServiceImageGallery)
    {
        parent::__construct();
        $this->setModel($partnerPosServiceImageGallery);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return PartnerPosServiceImageGallery::find($id);
    }

    public function delete($partnerPosServiceImageGallery)
    {
        return $partnerPosServiceImageGallery->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findWithTrashed($id)
    {
        return PartnerPosServiceImageGallery::withTrashed()->find($id);
    }

}