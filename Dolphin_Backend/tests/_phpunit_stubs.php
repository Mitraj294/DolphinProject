<?php
/**
 * Minimal PHPUnit stubs to help static analyzers (Psalm/PHPStan/IDE)
 * recognize PHPUnit's TestCase and a few commonly used assertion methods
 * when phpunit isn't installed in vendor (for example in CI or dev setups
 * where --no-dev was used).
 *
 * This file is intentionally lightweight and only declares signatures
 * for assertions referenced by the project's tests. It should not be
 * autoloaded at runtime for production code. If you later install
 * phpunit in vendor, you may remove this file.
 */

namespace PHPUnit\Framework;

abstract class TestCase
{
    /**
     * Asserts that the value is not null.
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotNull($actual, string $message = ''): void
    {
    }

    /**
     * Asserts that two variables are equal.
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertEquals($expected, $actual, string $message = ''): void
    {
    }

    /**
     * Asserts that a condition is true.
     * @param bool $condition
     * @param string $message
     */
    public function assertTrue(bool $condition, string $message = ''): void
    {
    }
}
