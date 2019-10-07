<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\Tests;

use AwdStudio\ServiceBuses\Core\Handing\HandlerLocator;
use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class BusTestCase extends TestCase
{

    /**
     * @return MockObject|HandlerLocator
     */
    protected function getHandlersMock(): MockObject
    {
        return $this->getMockBuilder(HandlerLocator::class)->getMock();
    }

    /**
     * @return MockObject|MiddlewareChain
     */
    protected function getMiddlewareMock(): MockObject
    {
        return $this->getMockBuilder(MiddlewareChain::class)->getMock();
    }

    /**
     * @return MockObject|ContainerInterface
     */
    protected function getContainerMock(): MockObject
    {
        return $this->getMockBuilder(ContainerInterface::class)->getMock();
    }

}
