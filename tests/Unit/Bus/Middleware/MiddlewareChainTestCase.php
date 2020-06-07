<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\Middleware\CallbackMiddlewareChain;
use AwdStudio\Tests\BusTestCase;

abstract class MiddlewareChainTestCase extends BusTestCase
{
    /** @var \AwdStudio\Bus\Middleware\CallbackMiddlewareChain */
    protected $instance;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    protected $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new CallbackMiddlewareChain($this->handlersProphecy->reveal());
    }
}
