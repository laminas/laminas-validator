<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

class MyBarcode1 extends AbstractAdapter
{
    public function __construct()
    {
        $this->setLength(-1);
        $this->setCharacters(0);
        $this->setChecksum('invalid');
    }
}
