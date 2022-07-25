<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

class MyBarcode2 extends AbstractAdapter
{
    public function __construct()
    {
        $this->setLength([1, 3, 6]);
        $this->setCharacters(128);
        $this->setChecksum('_mod10');
    }
}
