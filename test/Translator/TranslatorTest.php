<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Translator;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\Translator\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;

class TranslatorTest extends TestCase
{
    /** @var Translator */
    protected $translator;

    /** @var TranslatorInterface|MockObject */
    protected $i18nTranslator;

    public function setUp(): void
    {
        $this->i18nTranslator = $this->createMock(I18nTranslator::class);
        $this->translator     = new Translator($this->i18nTranslator);
    }

    public function testIsAnI18nTranslator(): void
    {
        $this->assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    public function testIsAValidatorTranslator(): void
    {
        $this->assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    /**
     * @throws ReflectionException
     */
    public function testCanRetrieveComposedTranslator(): void
    {
        $prop = new ReflectionProperty($this->translator, 'translator');

        $this->assertSame($this->i18nTranslator, $prop->getValue($this->translator));
    }
}
