<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

final class Gtin12 extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(12);
        $this->setCharacters('0123456789');
        $this->setChecksum('gtin');
    }
}
