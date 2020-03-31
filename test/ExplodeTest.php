<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Callback;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Explode;
use Laminas\Validator\InArray;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class ExplodeTest extends TestCase
{
    public function testRaisesExceptionWhenValidatorIsMissing()
    {
        $validator = new Explode();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('validator');
        $validator->isValid('foo,bar');
    }

    public function getExpectedData()
    {
        return [
            //    value              delim break  N  valid  messages                   expects
            ['foo,bar,dev,null', ',', false, 4, true,  [],                   true],
            ['foo,bar,dev,null', ',', true,  1, false, ['X'],                false],
            ['foo,bar,dev,null', ',', false, 4, false, ['X'],                false],
            ['foo,bar,dev,null', ';', false, 1, true,  [],                   true],
            ['foo;bar,dev;null', ',', false, 2, true,  [],                   true],
            ['foo;bar,dev;null', ',', false, 2, false, ['X'],                false],
            ['foo;bar;dev;null', ';', false, 4, true,  [],                   true],
            ['foo',              ',', false, 1, true,  [],                   true],
            ['foo',              ',', false, 1, false, ['X'],                false],
            ['foo',              ',', true,  1, false, ['X'],                false],
            [['a', 'b'],   null, false, 2, true,  [],                   true],
            [['a', 'b'],   null, false, 2, false, ['X'],                false],
            ['foo',             null, false, 1, true,  [],                   true],
            [1,                  ',', false, 1, true,  [],                   true],
            [null,               ',', false, 1, true,  [],                   true],
            [new \stdClass(),    ',', false, 1, true,  [],                   true],
            [new \ArrayObject(['a', 'b']), null, false, 2, true,  [],   true],
        ];
    }

    /**
     * @dataProvider getExpectedData
     */
    public function testExpectedBehavior(
        $value,
        $delimiter,
        $breakOnFirst,
        $numIsValidCalls,
        $isValidReturn,
        $messages,
        $expects
    ) {
        $mockValidator = $this->createMock(ValidatorInterface::class);
        $mockValidator
            ->expects($this->exactly($numIsValidCalls))
            ->method('isValid')
            ->willReturn($isValidReturn);
        $mockValidator
            ->method('getMessages')
            ->willReturn('X');

        $validator = new Explode([
            'validator'           => $mockValidator,
            'valueDelimiter'      => $delimiter,
            'breakOnFirstFailure' => $breakOnFirst,
        ]);

        $this->assertEquals($expects, $validator->isValid($value));
        $this->assertEquals($messages, $validator->getMessages());
    }

    public function testGetMessagesReturnsDefaultValue()
    {
        $validator = new Explode();
        $this->assertEquals([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Explode([]);
        $this->assertAttributeEquals(
            $validator->getOption('messageTemplates'),
            'messageTemplates',
            $validator
        );
    }

    public function testEqualsMessageVariables()
    {
        $validator = new Explode([]);
        $this->assertAttributeEquals(
            $validator->getOption('messageVariables'),
            'messageVariables',
            $validator
        );
    }

    public function testSetValidatorAsArray()
    {
        $validator = new Explode();
        $validator->setValidator([
            'name' => 'inarray',
            'options' => [
                'haystack' => ['a', 'b', 'c'],
            ],
        ]);

        /** @var $inArrayValidator \Laminas\Validator\InArray */
        $inArrayValidator = $validator->getValidator();
        $this->assertInstanceOf(InArray::class, $inArrayValidator);
        $this->assertSame(
            ['a', 'b', 'c'],
            $inArrayValidator->getHaystack()
        );
    }

    public function testSetValidatorMissingName()
    {
        $validator = new Explode();
        $this->expectException(RuntimeException::class);
        $validator->setValidator([
            'options' => [],
        ]);
    }

    public function testSetValidatorInvalidParam()
    {
        $validator = new Explode();
        $this->expectException(RuntimeException::class);
        $validator->setValidator('inarray');
    }

    /**
     * @group Laminas-5796
     */
    public function testGetMessagesMultipleInvalid()
    {
        $validator = new Explode([
            'validator'           => new Regex(
                '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/'
            ),
            'valueDelimiter'      => ',',
            'breakOnFirstFailure' => false,
        ]);

        $messages = [
            0 => [
                'regexNotMatch' => 'The input does not match against pattern '
                    . "'/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/'",
            ],
        ];

        $this->assertFalse($validator->isValid('api-tools-devteam@zend.com,abc,defghij'));
        $this->assertEquals($messages, $validator->getMessages());
    }

    /**
     * Assert context is passed to composed validator
     */
    public function testIsValidPassContext()
    {
        $context       = 'context';
        $contextSame   = false;
        $validator = new Explode([
            'validator'           => new Callback(function ($v, $c) use ($context, &$contextSame) {
                $contextSame = $context === $c;
                return true;
            }),
            'valueDelimiter'      => ',',
            'breakOnFirstFailure' => false,
        ]);
        $this->assertTrue($validator->isValid('a,b,c', $context));
        $this->assertTrue($contextSame);
    }
}
