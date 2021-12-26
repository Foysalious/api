<?php namespace Tests\Mocks;

use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class MockAccountingEntryClient extends AccountingEntryClient
{
    public function get($uri, $data = null): bool
    {
        return true;
    }

    public function post($uri, $data): bool
    {
        return true;
    }

    public function put($uri, $data): bool
    {
        return true;
    }

    public function delete($uri): bool
    {
        return true;
    }
}
