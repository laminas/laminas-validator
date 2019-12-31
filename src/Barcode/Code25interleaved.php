<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Barcode;

/**
 * @category   Laminas
 * @package    Laminas_Validate
 */
class Code25interleaved extends AbstractAdapter
{
    /**
     * Constructor
     *
     * Sets check flag to false.
     */
    public function __construct()
    {
        $this->setLength('even');
        $this->setCharacters('0123456789');
        $this->setChecksum('code25');
        $this->useChecksum(false);
    }
}
