<?php

declare(strict_types=1);

namespace AwdStudio\Query;

/**
 * Provides an interface for implementing the Query Bus pattern from the CQRS architectural principe.
 */
interface QueryBus
{
    /**
     * Handles a query with a handler.
     *
     * If a handler is not provided - throws an exception.
     *
     * A query can be any of PHP plain objects.
     * According to the pattern, it mustn return some result,
     * so that a signature of a handler should be provided locally.
     *
     * A bus also can get additional parameters,
     * that will be passed to the handlers,
     * if they allow to provide them.
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     */
    public function handle(object $query): mixed;
}
