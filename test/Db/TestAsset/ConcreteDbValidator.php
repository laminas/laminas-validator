<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db\TestAsset;

use Laminas\Validator\Db\AbstractDb;

final class ConcreteDbValidator extends AbstractDb
{
    public const FOO_MESSAGE = 'fooMessage';
    public const BAR_MESSAGE = 'barMessage';

    /** @var array<string, string> */
    protected $messageTemplates = [
        'fooMessage' => '%value% was passed',
        'barMessage' => '%value% was wrong',
    ];

    /** @var array<never, never> */
    protected $options = [];

    public function isValid(mixed $value): bool
    {
        $this->setValue($value);
        $this->error(self::FOO_MESSAGE);
        $this->error(self::BAR_MESSAGE);

        return false;
    }
}
