<?php

declare(strict_types=1);

namespace Laminas\Validator;

use function in_array;
use function is_int;
use function is_string;
use function preg_match;
use function quotemeta;
use function str_replace;
use function strlen;
use function substr;

class Isbn extends AbstractValidator
{
    public const AUTO    = 'auto';
    public const ISBN10  = '10';
    public const ISBN13  = '13';
    public const INVALID = 'isbnInvalid';
    public const NO_ISBN = 'isbnNoIsbn';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String or integer expected',
        self::NO_ISBN => 'The input is not a valid ISBN number',
    ];

    /** @var array<string, mixed> */
    protected $options = [
        'type'      => self::AUTO, // Allowed type
        'separator' => '', // Separator character
    ];

    /**
     * Detect input format.
     *
     * @return null|string
     */
    protected function detectFormat()
    {
        // prepare separator and pattern list
        $sep      = quotemeta($this->getSeparator());
        $patterns = [];
        $lengths  = [];
        $type     = $this->getType();

        // check for ISBN-10
        if ($type === self::ISBN10 || $type === self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{9}[0-9X]{1}$/';
                $length  = 10;
            } else {
                $pattern = "/^[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9X]{1}$/";
                $length  = 13;
            }

            $patterns[$pattern] = self::ISBN10;
            $lengths[$pattern]  = $length;
        }

        // check for ISBN-13
        if ($type === self::ISBN13 || $type === self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{13}$/';
                $length  = 13;
            } else {
                // @codingStandardsIgnoreStart
                $pattern = "/^[0-9]{1,9}[{$sep}]{1}[0-9]{1,5}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1}$/";
                // @codingStandardsIgnoreEnd
                $length = 17;
            }

            $patterns[$pattern] = self::ISBN13;
            $lengths[$pattern]  = $length;
        }

        // check pattern list
        foreach ($patterns as $pattern => $type) {
            if ((strlen($this->getValue()) === $lengths[$pattern]) && preg_match($pattern, $this->getValue())) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Returns true if and only if $value is a valid ISBN.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value) && ! is_int($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $value         = (string) $value;
        $originalValue = $value;
        $this->setValue($value);

        switch ($this->detectFormat()) {
            case self::ISBN10:
                $isbn = new Isbn\Isbn10();
                break;

            case self::ISBN13:
                $isbn = new Isbn\Isbn13();
                break;

            default:
                $this->error(self::NO_ISBN);
                return false;
        }

        $value    = str_replace($this->getSeparator(), '', $value);
        $checksum = $isbn->getChecksum($value);

        // validate
        if (substr($originalValue, -1) !== (string) $checksum) {
            $this->error(self::NO_ISBN);
            return false;
        }
        return true;
    }

    /**
     * Set separator characters.
     *
     * It is allowed only empty string, hyphen and space.
     *
     * @param  string $separator
     * @throws Exception\InvalidArgumentException When $separator is not valid.
     * @return $this Provides a fluent interface
     */
    public function setSeparator($separator)
    {
        // check separator
        if (! in_array($separator, ['-', ' ', ''])) {
            throw new Exception\InvalidArgumentException('Invalid ISBN separator.');
        }

        $this->options['separator'] = $separator;
        return $this;
    }

    /**
     * Get separator characters.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->options['separator'];
    }

    /**
     * Set allowed ISBN type.
     *
     * @param  string $type
     * @throws Exception\InvalidArgumentException When $type is not valid.
     * @return $this Provides a fluent interface
     */
    public function setType($type)
    {
        // check type
        if (! in_array($type, [self::AUTO, self::ISBN10, self::ISBN13])) {
            throw new Exception\InvalidArgumentException('Invalid ISBN type');
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Get allowed ISBN type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }
}
