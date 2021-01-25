<?php

namespace Laminas\Validator;

trait ValidatorOptionsTrait
{
    public function argumentsAsArray(array $keys, array $args) : array
    {
        $this->shouldHaveOnlyStringValues($keys);
        $result = [];
        foreach ($args as $arg) {
            $result[array_shift($keys)] = $arg;
        }
        return $result;
    }

    private function shouldHaveOnlyStringValues(array $keys) : void
    {
        foreach ($keys as $key) {
            if (! is_string($key)) {
                throw new \InvalidArgumentException('The values of $keys should be only strings');
            }
        }
    }
}
