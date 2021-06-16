<?php

namespace App\Sheba\NeoBanking\Repositories;

use App\Sheba\NeoBanking\Constants\ThirdPartyLog;
use Sheba\Dal\NeoBankingThirdPartyLog\Contract as Repository;
use Sheba\ModificationFields;
use Exception;

class NeoBankingThirdPartyLogRepository
{
    use ModificationFields;

    /** @var Repository $repository */
    private $repository;

    private $request, $response, $from, $partner_id, $others;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function setFrom($from)
    {
        if (!in_array($from,ThirdPartyLog::get())) {
            throw new Exception('Sorry! Invalid Third Party Name.');
        }

        $this->from = $from;
        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function store()
    {
        return $this->repository->create(
            $this->withCreateModificationField($this->generateData())
        );
    }

    private function generateData()
    {
        return [
            "request" => $this->request,
            "response" => $this->response,
            "from" => $this->from,
            "others" => $this->others,
            "partner_id" => $this->partner_id
        ];
    }


    /**
     * @param $partner_id
     * @return $this
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }


    /**
     * @param $others
     * @return $this
     */
    public function setOthers($others)
    {
        $this->others = $others;
        return $this;
    }
}