<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use AwdStudio\Bus\MiddlewareChain;

/**
 * @coversDefaultClass \AwdStudio\Bus\Middleware\CallbackMiddlewareChain
 */
final class MiddlewareChainInstantiatingTest extends MiddlewareChainTestCase
{
    /**
     * @covers ::__construct
     */
    public function testMustImplementAMiddleware(): void
    {
        $this->assertInstanceOf(MiddlewareChain::class, $this->instance);
    }
}
