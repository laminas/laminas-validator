<?php

namespace Laminas\Db\ResultSet;

use ArrayObject;

use function in_array;
use function is_array;
use function is_object;
use function method_exists;

/**
 * Test shim for PHP 8.1 compatibility
 *
 * This class replaces the laminas-db equivalent during development only, for
 * purposes of testing against PHP 8.1.
 *
 * @todo Remove when laminas-db has a release targetting PHP 8.1.
 */
class ResultSet extends AbstractResultSet
{
    public const TYPE_ARRAYOBJECT = 'arrayobject';
    public const TYPE_ARRAY       = 'array';

    /**
     * Allowed return types
     *
     * @var array
     */
    protected $allowedReturnTypes = [
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    ];

    /** @var ArrayObject */
    protected $arrayObjectPrototype;

    /**
     * Return type to use when returning an object from the set
     *
     * @var ResultSet::TYPE_ARRAYOBJECT|ResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

    /**
     * Constructor
     *
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct($returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        if (in_array($returnType, $this->allowedReturnTypes, true)) {
            $this->returnType = $returnType;
        } else {
            $this->returnType = self::TYPE_ARRAYOBJECT;
        }
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype($arrayObjectPrototype ?: new ArrayObject([], ArrayObject::ARRAY_AS_PROPS));
        }
    }

    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        if (
            ! is_object($arrayObjectPrototype)
            || (
                ! $arrayObjectPrototype instanceof ArrayObject
                && ! method_exists($arrayObjectPrototype, 'exchangeArray')
            )
        ) {
            throw new Exception\InvalidArgumentException(
                'Object must be of type ArrayObject, or at least implement exchangeArray'
            );
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }

    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return array|ArrayObject|null
     */
    public function current(): mixed
    {
        $data = parent::current();

        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var ArrayObject $ao */
            $ao = clone $this->arrayObjectPrototype;
            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }

        return $data;
    }
}
