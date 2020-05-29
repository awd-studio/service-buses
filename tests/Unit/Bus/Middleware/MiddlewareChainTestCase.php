<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\MiddlewareChain;
use AwdStudio\Tests\BusTestCase;

abstract class MiddlewareChainTestCase extends BusTestCase
{
    /** @var \AwdStudio\Bus\MiddlewareChain */
    protected $instance;

    /** @var \AwdStudio\Bus\Handlers|\Prophecy\Prophecy\ObjectProphecy */
    protected $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(Handlers::class);

        $this->instance = new MiddlewareChain($this->handlersProphecy->reveal());
    }
}
