<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Between;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Timezone;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function array_shift;
use function serialize;
use function strstr;
use function unserialize;

final class ValidatorChainTest extends TestCase
{
    private ValidatorChain $validator;

    protected function setUp(): void
    {
        parent::setUp();

        AbstractValidator::setMessageLength(-1);
        $this->validator = new ValidatorChain();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AbstractValidator::setDefaultTranslator(null);
        AbstractValidator::setMessageLength(-1);
    }

    public function populateValidatorChain(): void
    {
        $this->validator->attach(new NotEmpty());
        $this->validator->attach(new Between(1, 5));
    }

    public function testValidatorChainIsEmptyByDefault(): void
    {
        self::assertCount(0, $this->validator->getValidators());
    }

    /**
     * Ensures expected results from empty validator chain
     */
    public function testEmpty(): void
    {
        self::assertSame([], $this->validator->getMessages());
        self::assertTrue($this->validator->isValid('something'));
    }

    /**
     * Ensures expected behavior from a validator known to succeed
     */
    public function testTrue(): void
    {
        $this->validator->attach($this->getValidatorTrue());

        self::assertTrue($this->validator->isValid(null));
        self::assertSame([], $this->validator->getMessages());
    }

    /**
     * Ensures expected behavior from a validator known to fail
     */
    public function testFalse(): void
    {
        $this->validator->attach($this->getValidatorFalse());

        self::assertFalse($this->validator->isValid(null));
        self::assertSame(['error' => 'validation failed'], $this->validator->getMessages());
    }

    /**
     * Ensures that a validator may break the chain
     */
    public function testBreakChainOnFailure(): void
    {
        $this->validator
            ->attach($this->getValidatorFalse(), true)
            ->attach($this->getValidatorFalse());

        self::assertFalse($this->validator->isValid(null));
        self::assertSame(['error' => 'validation failed'], $this->validator->getMessages());
    }

    public function testAllowsPrependingValidators(): void
    {
        $this->validator
            ->attach($this->getValidatorTrue())
            ->prependValidator($this->getValidatorFalse(), true);

        self::assertFalse($this->validator->isValid(true));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('error', $messages);
    }

    public function testAllowsPrependingValidatorsByName(): void
    {
        $this->validator
            ->attach($this->getValidatorTrue())
            ->prependByName('NotEmpty', [], true);

        self::assertFalse($this->validator->isValid(''));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('isEmpty', $messages);
    }

    #[Group('6386')]
    #[Group('6496')]
    public function testValidatorsAreExecutedAccordingToPriority(): void
    {
        $this->validator
            ->attach($this->getValidatorTrue(), false, 1000)
            ->attach($this->getValidatorFalse(), true, 2000);

        self::assertFalse($this->validator->isValid(true));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('error', $messages);
    }

    #[Group('6386')]
    #[Group('6496')]
    public function testPrependValidatorsAreExecutedAccordingToPriority(): void
    {
        $this->validator
            ->attach($this->getValidatorTrue(), false, 1000)
            ->prependValidator($this->getValidatorFalse(), true);

        self::assertFalse($this->validator->isValid(true));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('error', $messages);
    }

    #[Group('6386')]
    #[Group('6496')]
    public function testMergeValidatorChains(): void
    {
        $mergedValidatorChain = new ValidatorChain();

        $mergedValidatorChain->attach($this->getValidatorTrue());
        $this->validator->attach($this->getValidatorTrue());

        $this->validator->merge($mergedValidatorChain);

        self::assertCount(2, $this->validator->getValidators());
    }

    #[Group('6386')]
    #[Group('6496')]
    public function testValidatorChainIsCloneable(): void
    {
        $this->validator->attach(new NotEmpty());

        self::assertCount(1, $this->validator->getValidators());

        $clonedValidatorChain = clone $this->validator;

        self::assertCount(1, $clonedValidatorChain->getValidators());

        $clonedValidatorChain->attach(new NotEmpty());

        self::assertCount(1, $this->validator->getValidators());
        self::assertCount(2, $clonedValidatorChain->getValidators());
    }

    public function testCountGivesCountOfAttachedValidators(): void
    {
        $this->populateValidatorChain();

        self::assertCount(2, $this->validator->getValidators());
    }

    /**
     * Handle file not found errors
     */
    #[Group('Laminas-2724')]
    public function handleNotFoundError(int $errnum, string $errstr): void
    {
        if (strstr($errstr, 'No such file') !== false) {
            $this->error = true;
        }
    }

    /**
     * @return ValidatorInterface&MockObject
     */
    public function getValidatorTrue(): ValidatorInterface
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects(self::any())
            ->method('isValid')
            ->willReturn(true);

        return $validator;
    }

    /**
     * @return ValidatorInterface&MockObject
     */
    public function getValidatorFalse(): ValidatorInterface
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects(self::any())
            ->method('isValid')
            ->willReturn(false);

        $validator
            ->expects(self::any())
            ->method('getMessages')
            ->willReturn(['error' => 'validation failed']);

        return $validator;
    }

    #[Group('Laminas-412')]
    public function testCanAttachMultipleValidatorsOfTheSameTypeAsDiscreteInstances(): void
    {
        $this->validator->attachByName('Callback', [
            'callback' => static fn($value): bool => true,
            'messages' => [
                'callbackValue' => 'This should not be seen in the messages',
            ],
        ]);
        $this->validator->attachByName('Callback', [
            'callback' => static fn($value): bool => false,
            'messages' => [
                'callbackValue' => 'Second callback trapped',
            ],
        ]);

        self::assertCount(2, $this->validator);

        $validators = $this->validator->getValidators();
        $compare    = null;
        foreach ($validators as $validator) {
            self::assertNotSame($compare, $validator);

            $compare = $validator;
        }

        self::assertFalse($this->validator->isValid('foo'));

        $messages = $this->validator->getMessages();

        self::assertContains('Second callback trapped', $messages);
        self::assertNotContains('This should not be seen in the messages', $messages);
    }

    public function testCanSerializeValidatorChain(): void
    {
        $this->populateValidatorChain();
        $serialized = serialize($this->validator);

        $unserialized = unserialize($serialized);

        self::assertInstanceOf(ValidatorChain::class, $unserialized);
        self::assertCount(2, $unserialized);
        self::assertFalse($unserialized->isValid(''));
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public static function breakChainFlags(): array
    {
        return [
            'underscores'    => ['break_chain_on_failure'],
            'no_underscores' => ['breakchainonfailure'],
        ];
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/89
     */
    #[DataProvider('breakChainFlags')]
    public function testAttachByNameAllowsSpecifyingBreakChainOnFailureFlagViaOptions(string $option): void
    {
        $this->validator->attachByName('GreaterThan', [
            $option => true,
            'min'   => 1,
        ]);

        self::assertCount(1, $this->validator);

        $validators = $this->validator->getValidators();
        $spec       = array_shift($validators);

        self::assertIsArray($spec);
        self::assertArrayHasKey('instance', $spec);

        $validator = $spec['instance'];

        self::assertInstanceOf(GreaterThan::class, $validator);
        self::assertArrayHasKey('breakChainOnFailure', $spec);
        self::assertTrue($spec['breakChainOnFailure']);
    }

    public function testGetValidatorsReturnsAnArrayOfQueueItems(): void
    {
        $empty   = new NotEmpty();
        $between = new Between(['min' => 10, 'max' => 20]);
        $expect  = [
            ['instance' => $empty, 'breakChainOnFailure' => false],
            ['instance' => $between, 'breakChainOnFailure' => false],
        ];

        $chain = new ValidatorChain();
        $chain->attach($empty);
        $chain->attach($between);

        self::assertSame($expect, $chain->getValidators());
    }

    public function testMessagesAreASingleDimensionHash(): void
    {
        $timezone = new Timezone();
        $between  = new Between(['min' => 10, 'max' => 20]);
        $chain    = new ValidatorChain();
        $chain->attach($timezone);
        $chain->attach($between);

        self::assertFalse($chain->isValid(0));

        $messages = $chain->getMessages();

        self::assertCount(2, $messages);
        self::assertContainsOnly('string', array_keys($messages));
        self::assertContainsOnly('string', $messages);
    }
}
