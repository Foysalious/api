<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use DB;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class Updater
{

    private $status;
    private $procurement;
    private $procurementRepository;


    public function __construct(ProcurementRepositoryInterface $procurement_repository)
    {
        $this->procurementRepository = $procurement_repository;
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
        $this->procurementRepository->update($this->procurement, ['status' => $this->status]);
    }
}