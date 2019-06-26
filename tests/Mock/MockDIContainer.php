<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\Tests\Mock;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

final class MockDIContainer extends BaseMock
{

    /**
     * {@inheritDoc}
     */
    protected function buildMock(): MockObject
    {
        return $this->test
            ->getMockBuilder(ContainerInterface::class)
            ->getMock();
    }

}
