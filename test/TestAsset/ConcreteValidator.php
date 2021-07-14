<?php

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\AbstractValidator;

class ConcreteValidator extends AbstractValidator
{
    public const FOO_MESSAGE = 'fooMessage';
    public const BAR_MESSAGE = 'barMessage';

    /** @var array<string, string> */
    protected $messageTemplates = [
        'fooMessage' => '%value% was passed',
        'barMessage' => '%value% was wrong',
    ];

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);
        $this->error(self::FOO_MESSAGE);
        $this->error(self::BAR_MESSAGE);
        return false;
    }
}
