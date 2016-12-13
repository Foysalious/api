<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoryTest extends TestCase {
    public function testGetCategory()
    {
        $this->json('GET', '/category')->seeJson(['code' => 200]);
    }

    public function testGetChildrenCategory()
    {
        $this->json('GET', '/category/2/children')->seeJson(['code' => 200]);
        $this->json('GET', '/category/3/children')->seeJson(['code' => 404]);
    }

    public function testGetParentCategory()
    {
        $this->json('GET', '/category/2/parent')->seeJson(['code' => 404]);
        $this->json('GET', '/category/3/parent')->seeJson(['code' => 200]);
    }
}
