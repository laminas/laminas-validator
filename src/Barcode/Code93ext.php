<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

final class Code93ext extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(-1);
        $this->setCharacters(128);
        $this->useChecksum(false);
    }
}
