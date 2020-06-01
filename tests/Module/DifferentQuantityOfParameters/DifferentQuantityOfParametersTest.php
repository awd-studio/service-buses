<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\DifferentQuantityOfParameters;

use AwdStudio\Bus\Handler\PsrContainerHandlerRegistry;
use AwdStudio\Bus\Middleware\MiddlewareChain;
use AwdStudio\Bus\MiddlewareBus;
use AwdStudio\Tests\BusTestCase;
use AwdStudio\Tests\Module\Stub\StubServiceLocator;

/**
 * @coversDefaultClass \AwdStudio\Bus\MiddlewareBus
 */
class DifferentQuantityOfParametersTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\MiddlewareBus */
    private $instance;

    /** @var \AwdStudio\Tests\Module\Stub\StubServiceLocator */
    private $handlerServiceLocator;

    /** @var \AwdStudio\Bus\Handler\PsrContainerHandlerRegistry */
    private $handlerRegistry;

    /** @var \AwdStudio\Tests\Module\Stub\StubServiceLocator */
    private $middlewareServiceLocator;

    /** @var \AwdStudio\Bus\Handler\PsrContainerHandlerRegistry */
    private $middlewareRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlerServiceLocator = new StubServiceLocator();
        $this->handlerRegistry = new PsrContainerHandlerRegistry($this->handlerServiceLocator);

        $this->middlewareServiceLocator = new StubServiceLocator();
        $this->middlewareRegistry = new PsrContainerHandlerRegistry($this->middlewareServiceLocator);
        $middlewareChain = new MiddlewareChain($this->middlewareRegistry);

        $this->instance = new class($this->handlerRegistry, $middlewareChain) extends MiddlewareBus {
            public function test(object $message, ...$extraParams): \Traversable
            {
                foreach ($this->chains($message, ...$extraParams) as $chain) {
                    yield $chain();
                }
            }
        };
    }

    /**
     * @covers ::chains
     * @dataProvider argumentsDataProvider
     */
    public function testMustAllowToUseDifferentQuantityOfAcceptableParameters(int ...$arguments): void
    {
        $this->handlerServiceLocator->add(MessageHandler::class);
        $this->handlerRegistry->register(Message::class, MessageHandler::class);
        $customHandler = static function (Message $message, ?int $foo = null, ?int $bar = null, ?int $baz = null)
        {
            $message->iCallIt('customHandler');
        };
        $this->handlerRegistry->add(Message::class, $customHandler);

        $this->middlewareServiceLocator->add(MessageMiddleware1::class);
        $this->middlewareServiceLocator->add(MessageMiddleware2::class);
        $this->middlewareRegistry->register(Message::class, MessageMiddleware1::class);
        $this->middlewareRegistry->register(Message::class, MessageMiddleware2::class);

        $message = new Message();

        \iterator_to_array($this->instance->test($message, ...$arguments));

        $this->assertCount(6, $message->visitors);
        $this->assertContains(MessageHandler::class, $message->visitors);
        $this->assertContains(MessageMiddleware1::class, $message->visitors);
        $this->assertContains(MessageMiddleware2::class, $message->visitors);
        $this->assertContains('customHandler', $message->visitors);
    }

    public function argumentsDataProvider(): array
    {
        return [
            [],
            [42],
            [42, 69],
            [42, 69, 1024],
        ];
    }
}
