<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\AbstractValidator;

class ConcreteValidator extends AbstractValidator
{
    const FOO_MESSAGE = 'fooMessage';

    protected $messageTemplates = array(
        'fooMessage' => '%value% was passed',
    );

    public function isValid($value)
    {
        $this->setValue($value);
        $this->error(self::FOO_MESSAGE);
        return false;
    }
}
