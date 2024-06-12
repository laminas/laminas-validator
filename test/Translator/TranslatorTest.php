<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Translator;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\Translator\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    /** @var Translator */
    protected $translator;

    /** @var TranslatorInterface&MockObject */
    protected $i18nTranslator;

    public function setUp(): void
    {
        $this->i18nTranslator = $this->createMock(I18nTranslator::class);
        $this->translator     = new Translator($this->i18nTranslator);
    }

    public function testTranslate(): void
    {
        $message    = 'This is the message';
        $textDomain = 'default';
        $locale     = 'en_US';

        $this->i18nTranslator->expects($this->once())
            ->method('translate')
            ->with($message, $textDomain, $locale)
            ->willReturn($message);

        $this->assertEquals(
            $message,
            $this->translator->translate(
                $message,
                $textDomain,
                $locale
            )
        );
    }

    public function testTranslatePlural(): void
    {
        $singular   = 'singular';
        $plural     = 'plural';
        $number     = 2;
        $textDomain = 'default';
        $locale     = 'en_US';

        $this->i18nTranslator->expects($this->once())
            ->method('translatePlural')
            ->with(
                $singular,
                $plural,
                $number,
                $textDomain,
                $locale
            )
            ->willReturn($singular);

        $this->assertEquals(
            $singular,
            $this->translator->translatePlural(
                $singular,
                $plural,
                $number,
                $textDomain,
                $locale
            )
        );
    }
}
