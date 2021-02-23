<?php namespace Sheba\Business\Procurement;

use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class DescriptionUpdater
{
    use ModificationFields;

    private $procurementRepo;
    private $procurement;
    private $description;

    public function __construct(ProcurementRepositoryInterface $procurement_repository)
    {
        $this->procurementRepo = $procurement_repository;
    }

    public function setProcurement($procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function update()
    {
        $data = [
            'long_description' => $this->description
        ];
        $this->procurementRepo->update($this->procurement, $this->withUpdateModificationField($data));
    }
}