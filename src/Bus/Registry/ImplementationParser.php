<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Registry;

interface ImplementationParser
{
    /**
     * Returns a list of all implementation of a class.
     *
     * @return string[]
     *
     * @psalm-param    class-string $messageId
     *
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   class-string[]
     *
     * @phpstan-return class-string[]
     */
    public function parse(string $messageId): array;
}
