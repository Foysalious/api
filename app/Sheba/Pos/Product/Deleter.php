<?php namespace Sheba\Pos\Product;


use Sheba\Pos\Repositories\PosServiceRepository;

class Deleter
{
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;

    /**
     * Deleter constructor.
     *
     * @param PosServiceRepository $service_repo
     */
    public function __construct(PosServiceRepository $service_repo)
    {
        $this->serviceRepo = $service_repo;
    }

    public function delete($id)
    {
        $partner_pos_service = $this->serviceRepo->find($id);
        return $this->serviceRepo->delete($partner_pos_service);
    }
}