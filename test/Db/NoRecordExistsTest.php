<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db;

use ArrayObject;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class NoRecordExistsTest extends TestCase
{
    /**
     * Return a Mock object for a Db result with rows
     *
     * @return Adapter
     */
    protected function getMockHasResult()
    {
        // mock the adapter, driver, and parts
        $mockConnection = $this->createMock(ConnectionInterface::class);

        // Mock has result
        $mockHasResultRow = new class extends ArrayObject {
            public ?string $one = null;
        };

        $mockHasResultRow->one = 'one';

        $mockHasResult = $this->createMock(ResultInterface::class);
        $mockHasResult
            ->method('current')
            ->willReturn($mockHasResultRow);

        $mockHasResultStatement = $this->createMock(StatementInterface::class);
        $mockHasResultStatement
            ->method('execute')
            ->willReturn($mockHasResult);

        $mockHasResultStatement
            ->method('getParameterContainer')
            ->willReturn(new ParameterContainer());

        $mockHasResultDriver = $this->createMock(DriverInterface::class);
        $mockHasResultDriver
            ->method('createStatement')
            ->willReturn($mockHasResultStatement);
        $mockHasResultDriver
            ->method('getConnection')
            ->willReturn($mockConnection);

        return $this->getMockBuilder(Adapter::class)
            ->setMethods(null)
            ->setConstructorArgs([$mockHasResultDriver])
            ->getMock();
    }

    /**
     * Return a Mock object for a Db result without rows
     *
     * @return Adapter
     */
    protected function getMockNoResult()
    {
        // mock the adapter, driver, and parts
        $mockConnection = $this->createMock(ConnectionInterface::class);

        $mockNoResult = $this->createMock(ResultInterface::class);
        $mockNoResult
            ->method('current')
            ->willReturn(null);

        $mockNoResultStatement = $this->createMock(StatementInterface::class);
        $mockNoResultStatement
            ->method('execute')
            ->willReturn($mockNoResult);

        $mockNoResultStatement
            ->method('getParameterContainer')
            ->willReturn(new ParameterContainer());

        $mockNoResultDriver = $this->createMock(DriverInterface::class);
        $mockNoResultDriver
            ->method('createStatement')
            ->willReturn($mockNoResultStatement);
        $mockNoResultDriver
            ->method('getConnection')
            ->willReturn($mockConnection);

        return $this->getMockBuilder(Adapter::class)
            ->setMethods(null)
            ->setConstructorArgs([$mockNoResultDriver])
            ->getMock();
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     */
    public function testBasicFindsRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value1'));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     */
    public function testBasicFindsNoRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockNoResult());
        $this->assertTrue($validator->isValid('nosuchvalue'));
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
        $this->assertFalse($validator->isValid('value3'));
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
        $this->assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithString(): void
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithStringNoRecord(): void
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockNoResult());
        $this->assertTrue($validator->isValid('nosuchvalue'));
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
        $this->assertFalse($validator->isValid('value1'));
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
        $this->assertTrue($validator->isValid('value1'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator        = new NoRecordExists('users', 'field1');
        $messageTemplates = [
            'noRecordFound' => 'No record matching the input was found',
            'recordFound'   => 'A record matching the input was found',
        ];
        $this->assertSame($messageTemplates, $validator->getOption('messageTemplates'));
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
