<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\Stub;

use Psr\Container\NotFoundExceptionInterface;

final class StubServiceLocatorNotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
