<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Digits;

/**
 * @category   Laminas
 * @package    Laminas_Validator
 * @subpackage UnitTests
 * @group      Laminas_Validator
 */
class DigitsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Digits
     */
    protected $validator;

    public function setUp()
    {
        $this->validator = new Digits();
    }

    /**
     * Ensures that the validator follows expected behavior for basic input values
     *
     * @return void
     */
    public function testExpectedResultsWithBasicInputValues()
    {
        $valuesExpected = array(
            'abc123'  => false,
            'abc 123' => false,
            'abcxyz'  => false,
            'AZ@#4.3' => false,
            '1.23'    => false,
            '0x9f'    => false,
            '123'     => true,
            '09'      => true,
            ''        => false
            );
        foreach ($valuesExpected as $input => $result) {
            $this->assertEquals($result, $this->validator->isValid($input));
        }
    }

    /**
     * Ensures that getMessages() returns expected initial value
     *
     * @return void
     */
    public function testMessagesEmptyInitially()
    {
        $this->assertEquals(array(), $this->validator->getMessages());
    }

    /**
     * @return void
     */
    public function testEmptyStringValueResultsInProperValidationFailureMessages()
    {
        $this->assertFalse($this->validator->isValid(''));
        $messages = $this->validator->getMessages();
        $arrayExpected = array(
            Digits::STRING_EMPTY => 'The input is an empty string'
            );
        $this->assertThat($messages, $this->identicalTo($arrayExpected));
    }

    /**
     * @return void
     */
    public function testInvalidValueResultsInProperValidationFailureMessages()
    {
        $this->assertFalse($this->validator->isValid('#'));
        $messages = $this->validator->getMessages();
        $arrayExpected = array(
            Digits::NOT_DIGITS => 'The input must contain only digits'
            );
        $this->assertThat($messages, $this->identicalTo($arrayExpected));
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation()
    {
        $this->assertFalse($this->validator->isValid(array(1 => 1)));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }
}
