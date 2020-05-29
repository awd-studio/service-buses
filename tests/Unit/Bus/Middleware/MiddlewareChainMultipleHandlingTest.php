<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\MiddlewareChain
 */
final class MiddlewareChainMultipleHandlingTest extends MiddlewareChainTestCase
{
    /**
     * @covers ::buildChain
     */
    public function testMustApplyAllOfMiddleware(): void
    {
        $message = new class() {
            /** @var string */
            public $m1;

            /** @var string */
            public $m2;

            /** @var string */
            public $m3;

            /** @var string */
            public $h;
        };

        $middleware1 = static function (object $message, callable $next): void
        {
            $message->m1 = 'foo';

            $next();
        };

        $middleware2 = static function (object $message, callable $next): void
        {
            $message->m2 = 'bar';

            $next();
        };

        $middleware3 = static function (object $message, callable $next): void
        {
            $message->m3 = 'baz';

            $next();
        };

        $handler = static function (object $message) { $message->h = 'done'; };

        $this->handlersProphecy
            ->get(Argument::type('object'))
            ->willYield([$middleware1, $middleware2, $middleware3]);

        $chain = $this->instance->buildChain($message, $handler);

        $chain();

        $this->assertSame('foo', $message->m1);
        $this->assertSame('bar', $message->m2);
        $this->assertSame('baz', $message->m3);
        $this->assertSame('done', $message->h);
    }
}
