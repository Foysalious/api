<?php namespace Tests\Unit;

use Closure;
use TestCase;

class UnitTestCase extends TestCase
{
    protected function shouldNotThrowException(Closure $closure)
    {
        try {
            $closure();
        } catch (\Exception $e) {
            $this->fail("Failed: " . $e->getMessage());
        }
    }
}
