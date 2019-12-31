<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Barcode;

class MyBarcode5
{
    public function __construct()
    {
        $setLength = 'odd';
        $setCharacters = 128;
        $setChecksum = '_mod10';
    }
}
