<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db\TestAsset;

use Laminas\Validator\Db\AbstractDb;

class ConcreteDbValidator extends AbstractDb
{
    const FOO_MESSAGE = 'fooMessage';
    const BAR_MESSAGE = 'barMessage';

    protected $messageTemplates = [
        'fooMessage' => '%value% was passed',
        'barMessage' => '%value% was wrong',
    ];

    public function isValid($value)
    {
        $this->setValue($value);
        $this->error(self::FOO_MESSAGE);
        $this->error(self::BAR_MESSAGE);
        return false;
    }
}
