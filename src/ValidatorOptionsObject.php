<?php


namespace Laminas\Validator;

class ValidatorOptionsObject
{
    private $keys;

    public function __construct(array $keys)
    {
        $this->shouldHaveOnlyStringValues($keys);
        $this->keys = $keys;
    }

    private function shouldHaveOnlyStringValues(array $keys) : void
    {
        foreach ($keys as $key) {
            if (! is_string($key)) {
                throw new \InvalidArgumentException('The values of $keys should be only strings');
            }
        }
    }

    public function argumentsAsArray(array $args) : array
    {
        $keys = $this->keys;
        $result = [];
        foreach ($args as $arg) {
            $result[array_shift($keys)] = $arg;
        }
        return $result;
    }
}
