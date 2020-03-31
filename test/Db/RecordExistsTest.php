<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db;

use ArrayObject;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
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
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class RecordExistsTest extends TestCase
{
    /**
     * Return a Mock object for a Db result with rows
     *
     * @return \Laminas\Db\Adapter\Adapter
     */
    protected function getMockHasResult()
    {
        // mock the adapter, driver, and parts
        $mockConnection = $this->createMock(ConnectionInterface::class);

        // Mock has result
        $mockHasResultRow      = new ArrayObject();
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
     * @return \Laminas\Db\Adapter\Adapter
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
     *
     * @return void
     */
    public function testBasicFindsRecord()
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'adapter' => $this->getMockHasResult(),
        ]);
        $this->assertTrue($validator->isValid('value1'));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     *
     * @return void
     */
    public function testBasicFindsNoRecord()
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'adapter' => $this->getMockNoResult(),
        ]);
        $this->assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     *
     * @return void
     */
    public function testExcludeWithArray()
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
        $this->assertTrue($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with an array
     *
     * @return void
     */
    public function testExcludeWithArrayNoRecord()
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
        $this->assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     * with a string
     *
     * @return void
     */
    public function testExcludeWithString()
    {
        $validator = new RecordExists([
            'table'   => 'users',
            'field'   => 'field1',
            'exclude' => 'id != 1',
            'adapter' => $this->getMockHasResult(),
        ]);
        $this->assertTrue($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with a string
     *
     * @return void
     */
    public function testExcludeWithStringNoRecord()
    {
        $validator = new RecordExists('users', 'field1', 'id != 1', $this->getMockNoResult());
        $this->assertFalse($validator->isValid('nosuchvalue'));
    }

    /**
     * @group Laminas-8863
     */
    public function testExcludeConstructor()
    {
        $validator = new RecordExists('users', 'field1', 'id != 1', $this->getMockHasResult());
        $this->assertTrue($validator->isValid('value3'));
    }

    /**
     * Test that the class throws an exception if no adapter is provided
     * and no default is set.
     *
     * @return void
     */
    public function testThrowsExceptionWithNoAdapter()
    {
        $validator = new RecordExists('users', 'field1', 'id != 1');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No database adapter present');
        $validator->isValid('nosuchvalue');
    }

    /**
     * Test that schemas are supported and run without error
     *
     * @return void
     */
    public function testWithSchema()
    {
        $validator = new RecordExists([
            'table' => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockHasResult());
        $this->assertTrue($validator->isValid('value1'));
    }

    /**
     * Test that schemas are supported and run without error
     *
     * @return void
     */
    public function testWithSchemaNoResult()
    {
        $validator = new RecordExists([
            'table' => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockNoResult());
        $this->assertFalse($validator->isValid('value1'));
    }

    /**
     * Test that the supplied table and schema are successfully passed to the select
     * statement
     */
    public function testSelectAcknowledgesTableAndSchema()
    {
        $validator = new RecordExists([
            'table' => 'users',
            'schema' => 'my',
        ], 'field1', null, $this->getMockHasResult());
        $table = $validator->getSelect()->getRawState('table');
        $this->assertInstanceOf(TableIdentifier::class, $table);
        $this->assertEquals(['users', 'my'], $table->getTableAndSchema());
    }

    public function testEqualsMessageTemplates()
    {
        $validator  = new RecordExists('users', 'field1');
        $this->assertAttributeEquals(
            $validator->getOption('messageTemplates'),
            'messageTemplates',
            $validator
        );
    }

    /**
     * @testdox Laminas\Validator\Db\RecordExists::getSelect
     */
    public function testGetSelect()
    {
        $validator = new RecordExists(
            [
                'table' => 'users',
                'schema' => 'my',
            ],
            'field1',
            [
                'field' => 'foo',
                'value' => 'bar',
            ],
            $this->getMockHasResult()
        );
        $select = $validator->getSelect();
        $this->assertInstanceOf(Select::class, $select);
        $this->assertEquals(
            'SELECT "my"."users"."field1" AS "field1" FROM "my"."users" WHERE "field1" = \'\' AND "foo" != \'bar\'',
            $select->getSqlString(new TrustingSql92Platform())
        );

        $sql = new Sql($this->getMockHasResult());
        $statement = $sql->prepareStatementForSqlObject($select);
        $parameters = $statement->getParameterContainer();
        $this->assertNull($parameters['where1']);
        $this->assertEquals($parameters['where2'], 'bar');
    }

    /**
     * @cover Laminas\Validator\Db\RecordExists::getSelect
     * @group Laminas-4521
     */
    public function testGetSelectWithSameValidatorTwice()
    {
        $validator = new RecordExists(
            [
                'table' => 'users',
                'schema' => 'my',
            ],
            'field1',
            [
                'field' => 'foo',
                'value' => 'bar',
            ],
            $this->getMockHasResult()
        );
        $select = $validator->getSelect();
        $this->assertInstanceOf(Select::class, $select);
        $this->assertEquals(
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
        $this->assertInstanceOf(Select::class, $select);
        $this->assertEquals(
            'SELECT "otherschema"."othertable"."fieldother" AS "fieldother" FROM "otherschema"."othertable" '
            . 'WHERE "fieldother" = \'\' AND "fieldexclude" != \'fieldvalueexclude\'',
            $select->getSqlString(new TrustingSql92Platform())
        );
    }
}
