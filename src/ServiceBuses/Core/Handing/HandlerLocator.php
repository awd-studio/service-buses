<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Core\Handing;

interface HandlerLocator
{

    /**
     * Returns a handler for a certain message.
     *
     * @param string $message A FQCN of class to handle.
     *
     * @psalm-param class-string $message
     *
     * @return callable[] The list handler for a message.
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     */
    public function get(string $message): iterable;

}
