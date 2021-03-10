<?php namespace Tests\Mocks;


use Sheba\ExpenseTracker\Repository\ExpenseTrackerClient;

class MockExpenseClient extends ExpenseTrackerClient
{
    /**
     * @param $uri
     * @return mixed
     */
    public function get($uri)
    {
        return "";
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function post($uri, $data)
    {
        return [
            "account" => [
                "id" => 1
            ]
        ];
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function put($uri, $data)
    {
        return "";
    }

    /**
     * @param $uri
     * @return mixed
     */
    public function delete($uri)
    {
        return "";
    }
}