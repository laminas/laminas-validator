<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\AbstractValidator;

final class ConcreteValidator extends AbstractValidator
{
    public const FOO_MESSAGE = 'fooMessage';
    public const BAR_MESSAGE = 'barMessage';

    public string $validValue = 'VALID';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        'fooMessage' => '%value% was passed',
        'barMessage' => '%value% was wrong',
    ];

    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if ($value === $this->validValue) {
            return true;
        }

        $this->error(self::FOO_MESSAGE);
        $this->error(self::BAR_MESSAGE);

        return false;
    }
}
