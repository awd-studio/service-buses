<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use AwdStudio\Bus\Middleware;

/**
 * @coversDefaultClass \AwdStudio\Bus\MiddlewareChain
 */
final class MiddlewareChainInstantiatingTest extends MiddlewareChainTestCase
{
    /**
     * @covers ::__construct
     */
    public function testMustImplementAMiddleware(): void
    {
        $this->assertInstanceOf(Middleware::class, $this->instance);
    }
}
