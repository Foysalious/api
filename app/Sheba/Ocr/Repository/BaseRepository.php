<?php namespace Sheba\Ocr\Repository;

use Sheba\ModificationFields;

class BaseRepository
{
    use ModificationFields;

    /** @var OcrClient $client */
    protected $client;

    /**
     * BaseRepository constructor.
     * @param OcrClient $client
     */
    public function __construct(OcrClient $client)
    {
        $this->client = $client;
    }
}
