<?php

namespace Tests\Unit;

use Closure;
use Exception;
use Tests\TestCase;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class UnitTestCase extends TestCase
{
    protected function shouldNotThrowException(Closure $closure)
    {
        try {
            $closure();
        } catch (Exception $e) {
            $this->fail("Failed: ".$e->getMessage());
        }
    }
}
