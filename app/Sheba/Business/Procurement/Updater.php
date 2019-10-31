<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class Updater
{

    private $status;
    private $procurement;
    private $statusLogCreator;
    private $procurementRepository;


    public function __construct(ProcurementRepositoryInterface $procurement_repository, Creator $creator)
    {
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function updateStatus()
    {
        try {
            $previous_status = $this->procurement->status;
            $procurement = $this->procurementRepository->update($this->procurement, ['status' => $this->status]);
            $this->statusLogCreator->setProcurement($this->procurement)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
        } catch (QueryException $e) {
            throw  $e;
        }
        return $procurement;
    }
}