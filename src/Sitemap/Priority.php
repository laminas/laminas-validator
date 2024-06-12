<?php

declare(strict_types=1);

namespace Laminas\Validator\Sitemap;

use Laminas\Validator\AbstractValidator;

use function is_numeric;

/**
 * Validates whether a given value is valid as a sitemap <priority> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 */
class Priority extends AbstractValidator
{
    /**
     * Validation key for not valid
     */
    public const NOT_VALID = 'sitemapPriorityNotValid';
    public const INVALID   = 'sitemapPriorityInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_VALID => 'The input is not a valid sitemap priority',
        self::INVALID   => 'Invalid type given. Numeric string, integer or float expected',
    ];

    /**
     * Validates if a string is valid as a sitemap priority
     *
     * @link http://www.sitemaps.org/protocol.php#prioritydef <priority>
     *
     * @param  string  $value  value to validate
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_numeric($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        $value = (float) $value;
        if ($value < 0 || $value > 1) {
            $this->error(self::NOT_VALID);
            return false;
        }

        return true;
    }
}
