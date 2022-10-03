<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db;

use ArrayObject;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Exception\RuntimeException;
use LaminasTest\Validator\Db\TestAsset\TrustingSql92Platform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Db\RecordExists
 */
final class RecordExistsTest extends TestCase
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
            ->expects(self::any())
            ->method('current')
            ->willReturn($mockHasResultRow);

        $mockHasResultStatement = $this->createMock(StatementInterface::class);
        $mockHasResultStatement
            ->expects(self::any())
            ->method('execute')
            ->willReturn($mockHasResult);

        $mockHasResultStatement
            ->expects(self::any())
            ->method('getParameterContainer')
            ->willReturn(new ParameterContainer());

        $mockHasResultDriver = $this->createMock(DriverInterface::class);
        $mockHasResultDriver
            ->expects(self::any())
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
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'adapter' => $this->getMockHasResult(),
        ]);

        self::assertTrue($validator->isValid('value1'));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     */
    public function testBasicFindsNoRecord(): void
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'adapter' => $this->getMockNoResult(),
        ]);

        self::assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     */
    public function testExcludeWithArray(): void
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'exclude' => [
                'field' => 'id',
                'value' => 1,
            ],
            'adapter' => $this->getMockHasResult(),
        ]);

        self::assertTrue($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with an array
     */
    public function testExcludeWithArrayNoRecord(): void
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'exclude' => [
                'field' => 'id',
                'value' => 1,
            ],
            'adapter' => $this->getMockNoResult(),
        ]);

        self::assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithString(): void
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'exclude' => 'id != 1',
            'adapter' => $this->getMockHasResult(),
        ]);

        self::assertTrue($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with a string
     */
    public function testExcludeWithStringNoRecord(): void
    {
        $validator = new RecordExists('users', 'field1', 'id != 1', $this->getMockNoResult());

        self::assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * @group Laminas-8863
     */
    public function testExcludeConstructor(): void
    {
        $validator = new RecordExists('users', 'field1', 'id != 1', $this->getMockHasResult());

        self::assertTrue($validator->isValid('value3'));
    }

    /**
     * Test that the class throws an exception if no adapter is provided
     * and no default is set.
     */
    public function testThrowsExceptionWithNoAdapter(): void
    {
        $validator = new RecordExists('users', 'field1', 'id != 1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No database adapter present');

        $validator->isValid('nosuchvalue');
    }

    /**
     * Test that schemas are supported and run without error
     */
    public function testWithSchema(): void
    {
        $validator = new RecordExists([
            'table'  => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockHasResult());

        self::assertTrue($validator->isValid('value1'));
    }

    /**
     * Test that schemas are supported and run without error
     */
    public function testWithSchemaNoResult(): void
    {
        $validator = new RecordExists([
            'table'  => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockNoResult());

        self::assertFalse($validator->isValid('value1'));
    }

    /**
     * Test that the supplied table and schema are successfully passed to the select
     * statement
     */
    public function testSelectAcknowledgesTableAndSchema(): void
    {
        $validator = new RecordExists([
            'table'  => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockHasResult());
        $table     = $validator->getSelect()->getRawState('table');

        self::assertInstanceOf(TableIdentifier::class, $table);
        self::assertSame(['users', 'my'], $table->getTableAndSchema());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator        = new RecordExists('users', 'field1');
        $messageTemplates = [
            'noRecordFound' => 'No record matching the input was found',
            'recordFound'   => 'A record matching the input was found',
        ];

        self::assertSame($messageTemplates, $validator->getOption('messageTemplates'));
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    /**
     * @testdox Laminas\Validator\Db\RecordExists::getSelect
     */
    public function testGetSelect(): void
    {
        $validator = new RecordExists(
            [
                'table'  => 'users',
                'schema' => 'my',
            ],
            'field1',
            [
                'field' => 'foo',
                'value' => 'bar',
            ],
            $this->getMockHasResult()
        );
        $select    = $validator->getSelect();

        self::assertInstanceOf(Select::class, $select);
        self::assertSame(
            'SELECT "my"."users"."field1" AS "field1" FROM "my"."users" WHERE "field1" = \'\' AND "foo" != \'bar\'',
            $select->getSqlString(new TrustingSql92Platform())
        );

        $sql        = new Sql($this->getMockHasResult());
        $statement  = $sql->prepareStatementForSqlObject($select);
        $parameters = $statement->getParameterContainer();

        self::assertNull($parameters['where1']);
        self::assertSame($parameters['where2'], 'bar');
    }

    /**
     * @cover Laminas\Validator\Db\RecordExists::getSelect
     * @group Laminas-4521
     */
    public function testGetSelectWithSameValidatorTwice(): void
    {
        $validator = new RecordExists(
            [
                'table'  => 'users',
                'schema' => 'my',
            ],
            'field1',
            [
                'field' => 'foo',
                'value' => 'bar',
            ],
            $this->getMockHasResult()
        );
        $select    = $validator->getSelect();

        self::assertInstanceOf(Select::class, $select);
        self::assertSame(
            'SELECT "my"."users"."field1" AS "field1" FROM "my"."users" WHERE "field1" = \'\' AND "foo" != \'bar\'',
            $select->getSqlString(new TrustingSql92Platform())
        );

        // same validator instance with changing properties
        $validator->setTable('othertable');
        $validator->setSchema('otherschema');
        $validator->setField('fieldother');
        $validator->setExclude([
            'field' => 'fieldexclude',
            'value' => 'fieldvalueexclude',
        ]);
        $select = $validator->getSelect();

        self::assertInstanceOf(Select::class, $select);
        self::assertSame(
            'SELECT "otherschema"."othertable"."fieldother" AS "fieldother" FROM "otherschema"."othertable" '
            . 'WHERE "fieldother" = \'\' AND "fieldexclude" != \'fieldvalueexclude\'',
            $select->getSqlString(new TrustingSql92Platform())
        );
    }
}
