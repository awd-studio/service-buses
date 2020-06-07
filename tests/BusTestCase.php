<?php

declare(strict_types=1);

namespace AwdStudio\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * A base test-case for library's tests.
 */
abstract class BusTestCase extends TestCase
{
    use ProphecyTrait;
}
