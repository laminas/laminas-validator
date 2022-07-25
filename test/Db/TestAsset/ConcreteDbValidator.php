<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db\TestAsset;

use Laminas\Validator\Db\AbstractDb;

class ConcreteDbValidator extends AbstractDb
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
