<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\I18n\Validator\Alpha;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Between;
use Laminas\Validator\StaticValidator;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class StaticValidatorTest extends TestCase
{
    /** @var Alpha */
    public $validator;

    /**
     * Creates a new validation object for each test method
     *
     * @return void
     */
    protected function setUp() : void
    {
        AbstractValidator::setDefaultTranslator(null);
        StaticValidator::setPluginManager(null);
        $this->validator = new Alpha();
    }

    protected function tearDown() : void
    {
        AbstractValidator::setDefaultTranslator(null);
        AbstractValidator::setMessageLength(-1);
    }

    public function testCanSetGlobalDefaultTranslator()
    {
        $translator = new TestAsset\Translator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    public function testGlobalDefaultTranslatorUsedWhenNoLocalTranslatorSet()
    {
        $this->testCanSetGlobalDefaultTranslator();
        $this->assertSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testLocalTranslatorPreferredOverGlobalTranslator()
    {
        $this->testCanSetGlobalDefaultTranslator();
        $translator = new TestAsset\Translator();
        $this->validator->setTranslator($translator);
        $this->assertNotSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testMaximumErrorMessageLength()
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->assertEquals(-1, AbstractValidator::getMessageLength());
        AbstractValidator::setMessageLength(10);
        $this->assertEquals(10, AbstractValidator::getMessageLength());

        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = [
            'Invalid type given. String expected' => 'This is the translated message for %value%',
        ];
        $translator = new TestAsset\Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        $this->validator->setTranslator($translator);
        $this->assertFalse($this->validator->isValid(123));
        $messages = $this->validator->getMessages();

        $this->assertArrayHasKey(Alpha::INVALID, $messages);
        $this->assertEquals('This is...', $messages[Alpha::INVALID]);
    }

    public function testSetGetMessageLengthLimitation()
    {
        AbstractValidator::setMessageLength(5);
        $this->assertEquals(5, AbstractValidator::getMessageLength());

        $valid = new Between(1, 10);
        $this->assertFalse($valid->isValid(24));
        $message = current($valid->getMessages());
        $this->assertLessThanOrEqual(5, strlen($message));
    }

    public function testSetGetDefaultTranslator()
    {
        $translator = new TestAsset\Translator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    /* plugin loading */

    public function testLazyLoadsValidatorPluginManagerByDefault()
    {
        $plugins = StaticValidator::getPluginManager();
        $this->assertInstanceOf(ValidatorPluginManager::class, $plugins);
    }

    public function testCanSetCustomPluginManager()
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder(ServiceManager::class)->getMock());
        StaticValidator::setPluginManager($plugins);
        $this->assertSame($plugins, StaticValidator::getPluginManager());
    }

    public function testPassingNullWhenSettingPluginManagerResetsPluginManager()
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder(ServiceManager::class)->getMock());
        StaticValidator::setPluginManager($plugins);
        $this->assertSame($plugins, StaticValidator::getPluginManager());
        StaticValidator::setPluginManager(null);
        $this->assertNotSame($plugins, StaticValidator::getPluginManager());
    }

    public function parameterizedData()
    {
        return [
            'valid-positive-range'   => [5, 'between', ['min' => 1, 'max' => 10], true],
            'valid-negative-range'   => [-5, 'between', ['min' => -10, 'max' => -1], true],
            'invalid-positive-range' => [-5, 'between', ['min' => 1, 'max' => 10], false],
            'invalid-negative-range' => [5, 'between', ['min' => -10, 'max' => -1], false],
        ];
    }

    /**
     * @dataProvider parameterizedData
     */
    public function testExecuteValidWithParameters($value, $validator, $options, $expected)
    {
        $this->assertSame($expected, StaticValidator::execute($value, $validator, $options));
    }

    public function invalidParameterizedData()
    {
        return [
            'positive-range' => [5, 'between', [1, 10]],
            'negative-range' => [-5, 'between', [-10, -1]],
        ];
    }

    /**
     * @dataProvider invalidParameterizedData
     */
    public function testExecuteRaisesExceptionForIndexedOptionsArray($value, $validator, $options)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('options');
        StaticValidator::execute($value, $validator, $options);
    }

    /**
     * Ensures that if we specify a validator class basename that doesn't
     * exist in the namespace, is() throws an exception.
     *
     * Refactored to conform with Laminas-2724.
     *
     * @group  Laminas-2724
     * @return void
     */
    public function testStaticFactoryClassNotFound()
    {
        $this->expectException(ServiceNotFoundException::class);
        StaticValidator::execute('1234', 'UnknownValidator');
    }
}
