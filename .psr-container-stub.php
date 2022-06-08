<?php

declare(strict_types=1);

namespace Psr\Container {
    /**
     * Provides automatic type inference for Psalm when retrieving a service from a container using a FQCN
     */
    interface ContainerInterface
    {
        /** @param string|class-string $id */
        public function has(string $id): bool;

        /**
         * @template T of object
         * @psalm-param string|class-string<T> $id
         * @psalm-return ($id is class-string ? T : mixed)
         */
        public function get(string $id): object;
    }
}
