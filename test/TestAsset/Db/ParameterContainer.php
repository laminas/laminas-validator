<?php

namespace Laminas\Db\Adapter;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;

use function array_key_exists;
use function array_values;
use function count;
use function current;
use function is_array;
use function is_int;
use function is_string;
use function key;
use function ltrim;
use function next;
use function reset;
use function strpos;

/**
 * Test shim for PHP 8.1 compatibility
 *
 * This class replaces the laminas-db equivalent during development only, for
 * purposes of testing against PHP 8.1.
 *
 * @todo Remove when laminas-db has a release targetting PHP 8.1.
 */
class ParameterContainer implements Iterator, ArrayAccess, Countable
{
    public const TYPE_AUTO    = 'auto';
    public const TYPE_NULL    = 'null';
    public const TYPE_DOUBLE  = 'double';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BINARY  = 'binary';
    public const TYPE_STRING  = 'string';
    public const TYPE_LOB     = 'lob';

    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /** @var array */
    protected $positions = [];

    /**
     * Errata
     *
     * @var array
     */
    protected $errata = [];

    /**
     * Max length
     *
     * @var array
     */
    protected $maxLength = [];

    /** @var array */
    protected $nameMapping = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->setFromArray($data);
        }
    }

    /**
     * Offset exists
     */
    public function offsetExists(mixed $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Offset get
     */
    public function offsetGet(mixed $name): mixed
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $normalizedName = ltrim($name, ':');
        if (
            isset($this->nameMapping[$normalizedName])
            && isset($this->data[$this->nameMapping[$normalizedName]])
        ) {
            return $this->data[$this->nameMapping[$normalizedName]];
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $from
     * @return void
     */
    public function offsetSetReference($name, $from)
    {
        $this->data[$name] = &$this->data[$from];
    }

    /**
     * Offset set
     *
     * @param string|int $name
     * @param mixed $errata
     * @param mixed $maxLength
     * @throws Exception\InvalidArgumentException
     */
    public function offsetSet(mixed $name, mixed $value, $errata = null, $maxLength = null): void
    {
        $position = false;

        // if integer, get name for this position
        if (is_int($name)) {
            if (isset($this->positions[$name])) {
                $position = $name;
                $name     = $this->positions[$name];
            } else {
                $name = (string) $name;
            }
        } elseif (is_string($name)) {
            // is a string:
            $normalizedName = ltrim($name, ':');
            if (isset($this->nameMapping[$normalizedName])) {
                // We have a mapping; get real name from it
                $name = $this->nameMapping[$normalizedName];
            }

            $position = array_key_exists($name, $this->data);

            // @todo: this assumes that any data begining with a ":" will be considered a parameter
            if (is_string($value) && strpos($value, ':') === 0) {
                // We have a named parameter; handle name mapping (container creation)
                $this->nameMapping[ltrim($value, ':')] = $name;
            }
        } elseif ($name === null) {
            $name = (string) count($this->data);
        } else {
            throw new Exception\InvalidArgumentException('Keys must be string, integer or null');
        }

        if ($position === false) {
            $this->positions[] = $name;
        }

        $this->data[$name] = $value;

        if ($errata) {
            $this->offsetSetErrata($name, $errata);
        }

        if ($maxLength) {
            $this->offsetSetMaxLength($name, $maxLength);
        }
    }

    /**
     * Offset unset
     */
    public function offsetUnset(mixed $name): void
    {
        if (is_int($name) && isset($this->positions[$name])) {
            $name = $this->positions[$name];
        }
        unset($this->data[$name]);
    }

    /**
     * Set from array
     *
     * @param  array $data
     * @return self Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        foreach ($data as $n => $v) {
            $this->offsetSet($n, $v);
        }
        return $this;
    }

    /**
     * Offset set max length
     *
     * @param string|int $name
     * @param mixed $maxLength
     */
    public function offsetSetMaxLength($name, $maxLength)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        $this->maxLength[$name] = $maxLength;
    }

    /**
     * Offset get max length
     *
     * @param  string|int $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function offsetGetMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (! array_key_exists($name, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->maxLength[$name];
    }

    /**
     * Offset has max length
     *
     * @param  string|int $name
     * @return bool
     */
    public function offsetHasMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        return isset($this->maxLength[$name]);
    }

    /**
     * Offset unset max length
     *
     * @param string|int $name
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetMaxLength($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (! array_key_exists($name, $this->maxLength)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->maxLength[$name] = null;
    }

    /**
     * Get max length iterator
     *
     * @return ArrayIterator
     */
    public function getMaxLengthIterator()
    {
        return new ArrayIterator($this->maxLength);
    }

    /**
     * Offset set errata
     *
     * @param string|int $name
     * @param mixed $errata
     */
    public function offsetSetErrata($name, $errata)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        $this->errata[$name] = $errata;
    }

    /**
     * Offset get errata
     *
     * @param  string|int $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function offsetGetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (! array_key_exists($name, $this->data)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->errata[$name];
    }

    /**
     * Offset has errata
     *
     * @param  string|int $name
     * @return bool
     */
    public function offsetHasErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        return isset($this->errata[$name]);
    }

    /**
     * Offset unset errata
     *
     * @param string|int $name
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnsetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (! array_key_exists($name, $this->errata)) {
            throw new Exception\InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->errata[$name] = null;
    }

    /**
     * Get errata iterator
     *
     * @return ArrayIterator
     */
    public function getErrataIterator()
    {
        return new ArrayIterator($this->errata);
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getNamedArray()
    {
        return $this->data;
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getPositionalArray()
    {
        return array_values($this->data);
    }

    /**
     * count
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Current
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Next
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Key
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Valid
     */
    public function valid(): bool
    {
        return current($this->data) !== false;
    }

    /**
     * Rewind
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @param array|ParameterContainer $parameters
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function merge($parameters)
    {
        if (! is_array($parameters) && ! $parameters instanceof ParameterContainer) {
            throw new Exception\InvalidArgumentException(
                '$parameters must be an array or an instance of ParameterContainer'
            );
        }

        if (count($parameters) === 0) {
            return $this;
        }

        if ($parameters instanceof ParameterContainer) {
            $parameters = $parameters->getNamedArray();
        }

        foreach ($parameters as $key => $value) {
            if (is_int($key)) {
                $key = null;
            }
            $this->offsetSet($key, $value);
        }
        return $this;
    }
}
