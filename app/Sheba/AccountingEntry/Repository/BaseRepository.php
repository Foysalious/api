<?php namespace App\Sheba\AccountingEntry\Repository;


use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\ModificationFields;

class BaseRepository
{
    use ModificationFields;

    /** @var AccountingEntryClient $client */
    protected $client;

    /**
     * BaseRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

}