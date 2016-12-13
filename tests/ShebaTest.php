<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShebaTest extends TestCase {
    public function testLocation()
    {
        $this->json('GET', '/locations')
            ->seeJson([
                'code' => 200
            ]);
    }

    public function testSearch()
    {
        $this->json('GET', '/search?s=adhvad')
            ->seeJson([
                'code' => 404
            ]);

        $this->json('GET', 'search?s=et')
            ->seeJson([
                'code' => 200
            ]);
    }
}
