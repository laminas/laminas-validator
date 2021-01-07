<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Between;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class ValidatorChainTest extends TestCase
{
    /**
     * @var ValidatorChain
     */
    protected $validator;

    protected function setUp() : void
    {
        AbstractValidator::setMessageLength(-1);
        $this->validator = new ValidatorChain();
    }

    protected function tearDown() : void
    {
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
        $this->assertCount(0, $this->validator->getValidators());
    }

    /**
     * Ensures expected results from empty validator chain
     *
     * @return void
     */
    public function testEmpty()
    {
        $this->assertEquals([], $this->validator->getMessages());
        $this->assertTrue($this->validator->isValid('something'));
    }

    /**
     * Ensures expected behavior from a validator known to succeed
     *
     * @return void
     */
    public function testTrue()
    {
        $this->validator->attach($this->getValidatorTrue());
        $this->assertTrue($this->validator->isValid(null));
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * Ensures expected behavior from a validator known to fail
     *
     * @return void
     */
    public function testFalse()
    {
        $this->validator->attach($this->getValidatorFalse());
        $this->assertFalse($this->validator->isValid(null));
        $this->assertEquals(['error' => 'validation failed'], $this->validator->getMessages());
    }

    /**
     * Ensures that a validator may break the chain
     *
     * @return void
     */
    public function testBreakChainOnFailure()
    {
        $this->validator->attach($this->getValidatorFalse(), true)
            ->attach($this->getValidatorFalse());
        $this->assertFalse($this->validator->isValid(null));
        $this->assertEquals(['error' => 'validation failed'], $this->validator->getMessages());
    }

    public function testAllowsPrependingValidators(): void
    {
        $this->validator->attach($this->getValidatorTrue())
            ->prependValidator($this->getValidatorFalse(), true);
        $this->assertFalse($this->validator->isValid(true));
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('error', $messages);
    }

    public function testAllowsPrependingValidatorsByName(): void
    {
        $this->validator->attach($this->getValidatorTrue())
            ->prependByName('NotEmpty', [], true);
        $this->assertFalse($this->validator->isValid(''));
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('isEmpty', $messages);
    }

    /**
     * @group 6386
     * @group 6496
     *
     * @return void
     */
    public function testValidatorsAreExecutedAccordingToPriority(): void
    {
        $this->validator->attach($this->getValidatorTrue(), false, 1000)
                        ->attach($this->getValidatorFalse(), true, 2000);
        $this->assertFalse($this->validator->isValid(true));
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('error', $messages);
    }

    /**
     * @group 6386
     * @group 6496
     *
     * @return void
     */
    public function testPrependValidatorsAreExecutedAccordingToPriority(): void
    {
        $this->validator->attach($this->getValidatorTrue(), false, 1000)
            ->prependValidator($this->getValidatorFalse(), true);
        $this->assertFalse($this->validator->isValid(true));
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('error', $messages);
    }

    /**
     * @group 6386
     * @group 6496
     *
     * @return void
     */
    public function testMergeValidatorChains(): void
    {
        $mergedValidatorChain = new ValidatorChain();

        $mergedValidatorChain->attach($this->getValidatorTrue());
        $this->validator->attach($this->getValidatorTrue());

        $this->validator->merge($mergedValidatorChain);

        $this->assertCount(2, $this->validator->getValidators());
    }

    /**
     * @group 6386
     * @group 6496
     *
     * @return void
     */
    public function testValidatorChainIsCloneable(): void
    {
        $this->validator->attach(new NotEmpty());

        $this->assertCount(1, $this->validator->getValidators());

        $clonedValidatorChain = clone $this->validator;

        $this->assertCount(1, $clonedValidatorChain->getValidators());

        $clonedValidatorChain->attach(new NotEmpty());

        $this->assertCount(1, $this->validator->getValidators());
        $this->assertCount(2, $clonedValidatorChain->getValidators());
    }

    public function testCountGivesCountOfAttachedValidators(): void
    {
        $this->populateValidatorChain();
        $this->assertCount(2, $this->validator->getValidators());
    }

    /**
     * Handle file not found errors
     *
     * @group  Laminas-2724
     * @param  int    $errnum
     * @param  string $errstr
     * @return void
     */
    public function handleNotFoundError($errnum, $errstr)
    {
        if (strstr($errstr, 'No such file')) {
            $this->error = true;
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Laminas\Validator\ValidatorInterface
     */
    public function getValidatorTrue()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('isValid')
            ->willReturn(true);
        return $validator;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Laminas\Validator\ValidatorInterface
     */
    public function getValidatorFalse()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('isValid')
            ->willReturn(false);
        $validator
            ->method('getMessages')
            ->willReturn(['error' => 'validation failed']);
        return $validator;
    }

    /**
     * @group Laminas-412
     *
     * @return void
     */
    public function testCanAttachMultipleValidatorsOfTheSameTypeAsDiscreteInstances(): void
    {
        $this->validator->attachByName('Callback', [
            'callback' => function ($value) {
                return true;
            },
            'messages' => [
                'callbackValue' => 'This should not be seen in the messages',
            ],
        ]);
        $this->validator->attachByName('Callback', [
            'callback' => function ($value) {
                return false;
            },
            'messages' => [
                'callbackValue' => 'Second callback trapped',
            ],
        ]);

        $this->assertCount(2, $this->validator);
        $validators = $this->validator->getValidators();
        $compare = null;
        foreach ($validators as $validator) {
            $this->assertNotSame($compare, $validator);
            $compare = $validator;
        }

        $this->assertFalse($this->validator->isValid('foo'));
        $messages = $this->validator->getMessages();
        $found    = false;
        $test     = 'Second callback trapped';
        foreach ($messages as $messageSet) {
            if (is_string($messageSet) && $messageSet === $test) {
                $found = true;
                break;
            }
            if (is_array($messageSet) && in_array('Second callback trapped', $messageSet)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testCanSerializeValidatorChain(): void
    {
        $this->populateValidatorChain();
        $serialized = serialize($this->validator);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(ValidatorChain::class, $unserialized);
        $this->assertCount(2, $unserialized);
        $this->assertFalse($unserialized->isValid(''));
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public function breakChainFlags(): array
    {
        return [
            'underscores' => ['break_chain_on_failure'],
            'no_underscores' => ['breakchainonfailure'],
        ];
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/89
     *
     * @dataProvider breakChainFlags
     */
    public function testAttachByNameAllowsSpecifyingBreakChainOnFailureFlagViaOptions($option): void
    {
        $this->validator->attachByName('GreaterThan', [
            $option => true,
            'min' => 1,
        ]);
        $this->assertCount(1, $this->validator);
        $validators = $this->validator->getValidators();
        $spec       = array_shift($validators);

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('instance', $spec);
        $validator = $spec['instance'];
        $this->assertInstanceOf(GreaterThan::class, $validator);
        $this->assertArrayHasKey('breakChainOnFailure', $spec);
        $this->assertTrue($spec['breakChainOnFailure']);
    }
}
