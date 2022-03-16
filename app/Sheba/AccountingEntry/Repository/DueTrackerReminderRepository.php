<?php

namespace App\Sheba\AccountingEntry\Repository;

use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerReminderRepository extends AccountingRepository
{

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param $data
     * @return void
     */
    public function createReminder($data)
    {
        return $data;
    }
}