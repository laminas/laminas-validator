<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db;

use ArrayObject;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NoRecordExistsTest extends TestCase
{
    /**
     * Return a Mock object for a Db result with rows
     *
     * @return Adapter&MockObject
     */
    protected function getMockHasResult(): Adapter
    {
        // Mock has result
        $mockHasResultRow = new class extends ArrayObject {
            public ?string $one = null;
        };

        $mockHasResultRow->one = 'one';

        $mockHasResult = $this->createMock(ResultInterface::class);
        $mockHasResult
            ->expects(self::once())
            ->method('current')
            ->willReturn($mockHasResultRow);

        $mockHasResultStatement = $this->createMock(StatementInterface::class);
        $mockHasResultStatement
            ->expects(self::once())
            ->method('execute')
            ->willReturn($mockHasResult);

        $mockHasResultStatement
            ->expects(self::exactly(2))
            ->method('getParameterContainer')
            ->willReturn(new ParameterContainer());

        $mockHasResultDriver = $this->createMock(DriverInterface::class);
        $mockHasResultDriver
            ->expects(self::once())
            ->method('createStatement')
            ->willReturn($mockHasResultStatement);
        $mockHasResultDriver
            ->expects(self::never())
            ->method('getConnection');

        return $this->getMockBuilder(Adapter::class)
            ->addMethods([])
            ->setConstructorArgs([$mockHasResultDriver])
            ->getMock();
    }

    /**
     * Return a Mock object for a Db result without rows
     *
     * @return Adapter&MockObject
     */
    protected function getMockNoResult(): Adapter
    {
        $mockNoResult = $this->createMock(ResultInterface::class);
        $mockNoResult
            ->expects(self::once())
            ->method('current')
            ->willReturn(null);

        $mockNoResultStatement = $this->createMock(StatementInterface::class);
        $mockNoResultStatement
            ->expects(self::once())
            ->method('execute')
            ->willReturn($mockNoResult);

        $mockNoResultStatement
            ->expects(self::exactly(2))
            ->method('getParameterContainer')
            ->willReturn(new ParameterContainer());

        $mockNoResultDriver = $this->createMock(DriverInterface::class);
        $mockNoResultDriver
            ->expects(self::once())
            ->method('createStatement')
            ->willReturn($mockNoResultStatement);
        $mockNoResultDriver
            ->expects(self::never())
            ->method('getConnection');

        return $this->getMockBuilder(Adapter::class)
            ->addMethods([])
            ->setConstructorArgs([$mockNoResultDriver])
            ->getMock();
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     */
    public function testBasicFindsRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockHasResult());

        self::assertFalse($validator->isValid('value1'));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     */
    public function testBasicFindsNoRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockNoResult());

        self::assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     */
    public function testExcludeWithArray(): void
    {
        $validator = new NoRecordExists(
            'users',
            'field1',
            ['field' => 'id', 'value' => 1],
            $this->getMockHasResult()
        );

        self::assertFalse($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with an array
     */
    public function testExcludeWithArrayNoRecord(): void
    {
        $validator = new NoRecordExists(
            'users',
            'field1',
            ['field' => 'id', 'value' => 1],
            $this->getMockNoResult()
        );

        self::assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithString(): void
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockHasResult());

        self::assertFalse($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithStringNoRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockNoResult());

        self::assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test that the class throws an exception if no adapter is provided
     * and no default is set.
     */
    public function testThrowsExceptionWithNoAdapter(): void
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No database adapter present');

        $validator->isValid('nosuchvalue');
    }

    /**
     * Test that schemas are supported and run without error
     */
    public function testWithSchema(): void
    {
        $validator = new NoRecordExists([
            'table'  => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockHasResult());

        self::assertFalse($validator->isValid('value1'));
    }

    /**
     * Test that schemas are supported and run without error
     */
    public function testWithSchemaNoResult(): void
    {
        $validator = new NoRecordExists([
            'table'  => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockNoResult());

        self::assertTrue($validator->isValid('value1'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator        = new NoRecordExists('users', 'field1');
        $messageTemplates = [
            'noRecordFound' => 'No record matching the input was found',
            'recordFound'   => 'A record matching the input was found',
        ];

        self::assertSame($messageTemplates, $validator->getOption('messageTemplates'));
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
