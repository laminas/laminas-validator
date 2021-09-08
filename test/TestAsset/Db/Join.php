<?php

namespace Laminas\Db\Sql;

use Countable;
use Iterator;

use function array_shift;
use function count;
use function is_array;
use function is_string;
use function key;
use function sprintf;

/**
 * Test shim for PHP 8.1 compatibility
 *
 * This class replaces the laminas-db equivalent during development only, for
 * purposes of testing against PHP 8.1.
 *
 * @todo Remove when laminas-db has a release targetting PHP 8.1.
 */
class Join implements Iterator, Countable
{
    public const JOIN_INNER       = 'inner';
    public const JOIN_OUTER       = 'outer';
    public const JOIN_FULL_OUTER  = 'full outer';
    public const JOIN_LEFT        = 'left';
    public const JOIN_RIGHT       = 'right';
    public const JOIN_RIGHT_OUTER = 'right outer';
    public const JOIN_LEFT_OUTER  = 'left outer';

    /**
     * Current iterator position.
     *
     * @var int
     */
    private $position = 0;

    /**
     * JOIN specifications
     *
     * @var array
     */
    protected $joins = [];

    /**
     * Initialize iterator position.
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * Rewind iterator.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Return current join specification.
     */
    public function current(): array
    {
        return $this->joins[$this->position];
    }

    /**
     * Return the current iterator index.
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Advance to the next JOIN specification.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Is the iterator at a valid position?
     */
    public function valid(): bool
    {
        return isset($this->joins[$this->position]);
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param string|array|TableIdentifier $name A table name on which to join, or a single
     *     element associative array, of the form alias => table, or TableIdentifier instance
     * @param string|Predicate\Expression $on A specification describing the fields to join on.
     * @param string|string[]|int|int[] $columns A single column name, an array
     *     of column names, or (a) specification(s) such as SQL_STAR representing
     *     the columns to join.
     * @param string $type The JOIN type to use; see the JOIN_* constants.
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException For invalid $name values.
     */
    public function join($name, $on, $columns = [Select::SQL_STAR], $type = self::JOIN_INNER)
    {
        if (is_array($name) && (! is_string(key($name)) || count($name) !== 1)) {
            throw new Exception\InvalidArgumentException(
                sprintf("join() expects '%s' as a single element associative array", array_shift($name))
            );
        }

        if (! is_array($columns)) {
            $columns = [$columns];
        }

        $this->joins[] = [
            'name'    => $name,
            'on'      => $on,
            'columns' => $columns,
            'type'    => $type ? $type : self::JOIN_INNER,
        ];

        return $this;
    }

    /**
     * Reset to an empty list of JOIN specifications.
     *
     * @return self Provides a fluent interface
     */
    public function reset()
    {
        $this->joins = [];
        return $this;
    }

    /**
     * Get count of attached predicates
     */
    public function count(): int
    {
        return count($this->joins);
    }
}
