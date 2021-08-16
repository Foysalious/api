<?php namespace Tests\Mocks;

use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class MockAccountingEntryClient extends AccountingEntryClient
{
    public function post($uri, $data)
    {
        return true;
    }

}