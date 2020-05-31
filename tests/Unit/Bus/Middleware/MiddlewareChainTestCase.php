<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\Middleware\MiddlewareChain;
use AwdStudio\Tests\BusTestCase;

abstract class MiddlewareChainTestCase extends BusTestCase
{
    /** @var \AwdStudio\Bus\Middleware\MiddlewareChain */
    protected $instance;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    protected $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new MiddlewareChain($this->handlersProphecy->reveal());
    }
}
