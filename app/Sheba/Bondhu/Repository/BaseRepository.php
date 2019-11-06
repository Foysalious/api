<?php namespace Sheba\Bondhu\Repository;


use App\Sheba\Bondhu\Repository\NidOcrClient;
use Sheba\ModificationFields;


class BaseRepository
{
    use ModificationFields;

    /** @var NidOcrClient $client */
    protected $client;

    /**
     * BaseRepository constructor.
     * @param NidOcrClient $client
     */
    public function __construct(NidOcrClient $client)
    {
        $this->client = $client;
    }

}
