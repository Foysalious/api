<?php namespace Tests\Mocks;

use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class MockAccountingEntryClient extends AccountingEntryClient
{
    public function get($uri, $data = null)
    {
        return true;
    }

    public function post($uri, $data)
    {
        return true;
    }

    public function put($uri, $data)
    {
        return true;
    }

    public function delete($uri)
    {
        return true;
    }

}