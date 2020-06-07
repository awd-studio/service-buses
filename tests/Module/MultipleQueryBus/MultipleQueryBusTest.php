<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\MultipleQueryBus;

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;
use AwdStudio\Bus\Middleware\CallbackMiddlewareChain;
use AwdStudio\Query\MiddlewareQueryBus;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\Query\MiddlewareQueryBus
 */
final class MultipleQueryBusTest extends BusTestCase
{
    /** @var \AwdStudio\Query\QueryBus */
    private $instance;

    /** @var \AwdStudio\Bus\Handler\InMemoryHandlerLocator */
    private $handlerRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlerRegistry = new InMemoryHandlerLocator();

        $this->instance = new MiddlewareQueryBus($this->handlerRegistry, new CallbackMiddlewareChain(new InMemoryHandlerLocator()));
    }

    /**
     * @covers ::handle
     */
    public function testMustReturnItemsFromAllHandlers(): void
    {
        $h1 = static function (): \Iterator
        {
            yield 'foo';
            yield 'bar';
        };

        $h2 = static function (): \Iterator
        {
            yield 'baz';
        };

        $h3 = static function (): \Iterator
        {
            yield 'quu';
            yield 'quuuu';
            yield 'quuuuuuu';
        };

        $aggregateServiceHandler = new class ($h1, $h2, $h3) {
            /** @var array */
            private $handlers;

            public function __construct(callable ...$handlers)
            {
                $this->handlers = $handlers;
            }

            public function __invoke(\stdClass $message): \Iterator
            {
                foreach ($this->handlers as $handler) {
                    foreach ($handler() as $result) {
                        yield $result;
                    }
                }
            }
        };

        $this->handlerRegistry->add(\stdClass::class, $aggregateServiceHandler);

        $result = \iterator_to_array($this->instance->handle(new \stdClass()));

        $this->assertCount(6, $result);
        $this->assertContains('foo', $result);
        $this->assertContains('bar', $result);
        $this->assertContains('baz', $result);
        $this->assertContains('quu', $result);
        $this->assertContains('quuuu', $result);
        $this->assertContains('quuuuuuu', $result);
    }
}
