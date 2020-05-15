<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\Tests\ServiceBuses\EventBus\Middleware\Multiple;

use AwdStudio\ServiceBuses\EventBus\EventBus;
use AwdStudio\ServiceBuses\Implementation\Handling\InMemoryHandlerLocator;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;
use PHPStan\Testing\TestCase;

final class EventBusMultipleMiddlewareReactionAfterHandlerTest extends TestCase
{

    public function testMustSupportMultipleMiddleware()
    {
        $eventBus = new EventBus(
            new InMemoryHandlerLocator([Event::class => [new EventHandler()]]),
            new Chain(new EventMiddleware1(), new EventMiddleware2())
        );

        $event = new Event();

        $eventBus->handle($event);

        $this->assertTrue($event->isFilledByHandler());
        $this->assertTrue($event->isFilledByMiddleware1());
        $this->assertTrue($event->isFilledByMiddleware2());
    }

}

class Event
{
    /** @var bool */
    private $filledByHandler = false;

    /** @var bool */
    private $filledByMiddleware1 = false;

    /** @var bool */
    private $filledByMiddleware2 = false;

    public function fillByHandler(EventHandler $handler): void
    {
        $this->filledByHandler = $handler->check($this);
    }

    public function fillByMiddleware1(EventMiddleware1 $middleware): void
    {
        $this->filledByMiddleware1 = $middleware->check($this);
    }

    public function fillByMiddleware2(EventMiddleware2 $middleware): void
    {
        $this->filledByMiddleware2 = $middleware->check($this);
    }

    public function isFilledByHandler(): bool
    {
        return $this->filledByHandler;
    }

    public function isFilledByMiddleware1(): bool
    {
        return $this->filledByMiddleware1;
    }

    public function isFilledByMiddleware2(): bool
    {
        return $this->filledByMiddleware2;
    }
}

abstract class EventComparator
{
    /** @var Event */
    protected $event;

    public function check(Event $event): bool
    {
        return $this->event === $event;
    }
}

class EventHandler extends EventComparator
{
    public function __invoke(Event $event): void
    {
        $this->event = $event;

        $event->fillByHandler($this);
    }
}

class EventMiddleware1 extends EventComparator
{
    public function __invoke(Event $event, callable $next)
    {
        $this->event = $event;

        $result = $next($event);
        $event->fillByMiddleware1($this);
        return $result;
    }
}

class EventMiddleware2 extends EventComparator
{
    public function __invoke(Event $event, callable $next)
    {
        $this->event = $event;

        $event->fillByMiddleware2($this);
        return $next($event);
    }
}
