<?php

namespace Laminas\Validator\Barcode;

class MyBarcode3 extends AbstractAdapter
{
    public function __construct()
    {
        $this->setLength([1, 3, 6, -1]);
        $this->setCharacters(128);
        $this->setChecksum('_mod10');
    }
}
