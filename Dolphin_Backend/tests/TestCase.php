<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Test case base class for application tests.
 *
 * This class extends Laravel's testing TestCase which in turn extends
 * PHPUnit\Framework\TestCase. Add an @extends annotation so static
 * analyzers (Psalm/PHPStan/IDE) correctly resolve PHPUnit assertion
 * methods (e.g. assertEquals, assertTrue) when used from tests.
 *
 * @extends \PHPUnit\Framework\TestCase
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
