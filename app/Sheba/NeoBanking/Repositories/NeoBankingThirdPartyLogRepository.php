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

    private $data, $dataType, $from;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function setFrom($from)
    {
        if (!in_array($from,ThirdPartyLog::THIRD_PARTY_FROM_LIST)) {
            throw new Exception('Sorry! Invalid Third Party Name.');
        }

        $this->from = $from;
        return $this;
    }

    public function setDataType($dataType)
    {
        if (!in_array($dataType,ThirdPartyLog::DATA_TYPE_LIST)) {
           throw new Exception('Sorry! Invalid Data Type.');
        }
        $this->dataType = $dataType;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function store()
    {
        return $this->repository->create(
            $this->withCreateModificationField(self::getData($this->data, $this->dataType, $this->from))
        );
    }

    private static function getData($data, $dataType, $from)
    {
        return [
            "data" => $data,
            "data_type" => $dataType,
            "from" => $from,
        ];
    }
}