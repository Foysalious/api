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

    private $request, $response, $from;

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
        dd($this->getData());
        return $this->repository->create(
            $this->withCreateModificationField($this->getData())
        );
    }

    private function getData()
    {
        return [
            "request" => $this->request,
            "response" => $this->response,
            "from" => $this->from,
        ];
    }
}