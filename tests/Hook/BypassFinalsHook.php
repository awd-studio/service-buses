<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\Tests\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

final class BypassFinalsHook implements BeforeTestHook
{

    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
