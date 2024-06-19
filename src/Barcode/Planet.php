<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

final class Planet extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength([12, 14]);
        $this->setCharacters('0123456789');
        $this->setChecksum('postnet');
    }
}
