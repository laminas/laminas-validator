<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

class MyBarcode4 extends AbstractAdapter
{
    public function __construct()
    {
        $this->setLength('odd');
        $this->setCharacters(128);
        $this->setChecksum('_mod10');
    }
}
