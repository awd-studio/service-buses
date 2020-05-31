<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\DifferentQuantityOfParameters;

final class Message
{
    /** @var string[] */
    public $visitors = [];

    public function iCallIt(string $visitor): void
    {
        $this->visitors[] = $visitor;
    }

    public function iWasHere(object $visitor): void
    {
        $this->visitors[] = \get_class($visitor);
    }
}
