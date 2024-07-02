<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\AbstractValidator;

final class ConcreteValidator extends AbstractValidator
{
    public const FOO_MESSAGE = 'fooMessage';
    public const BAR_MESSAGE = 'barMessage';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        'fooMessage' => '%value% was passed',
        'barMessage' => '%value% was wrong',
    ];

    public function isValid(mixed $value): bool
    {
        $this->setValue($value);
        $this->error(self::FOO_MESSAGE);
        $this->error(self::BAR_MESSAGE);

        return false;
    }
}
