<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\Tests\Mock;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class BaseMock
{

    /** @var \PHPUnit\Framework\TestCase */
    protected $test;

    /**
     * MockServiceHolder constructor.
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function __construct(TestCase $test)
    {
        $this->test = $test;
    }

    public static function getMock(TestCase $test): MockObject
    {
        return (new static($test))->buildMock();
    }


    /**
     * The mocking process.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|mixed
     */
    abstract protected function buildMock(): MockObject;

}
