<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db;

use ArrayObject;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Validator\Db\NoRecordExists;

/**
 * @group      Laminas_Validator
 */
class NoRecordExistsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return a Mock object for a Db result with rows
     *
     * @return \Laminas\Db\Adapter\Adapter
     */
    protected function getMockHasResult()
    {
        // mock the adapter, driver, and parts
        $mockConnection = $this->getMock('Laminas\Db\Adapter\Driver\ConnectionInterface');

        // Mock has result
        $mockHasResultRow      = new ArrayObject();
        $mockHasResultRow->one = 'one';

        $mockHasResult = $this->getMock('Laminas\Db\Adapter\Driver\ResultInterface');
        $mockHasResult->expects($this->any())
            ->method('current')
            ->will($this->returnValue($mockHasResultRow));

        $mockHasResultStatement = $this->getMock('Laminas\Db\Adapter\Driver\StatementInterface');
        $mockHasResultStatement->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockHasResult));

        $mockHasResultStatement->expects($this->any())
            ->method('getParameterContainer')
            ->will($this->returnValue(new ParameterContainer()));

        $mockHasResultDriver = $this->getMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockHasResultDriver->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($mockHasResultStatement));
        $mockHasResultDriver->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($mockConnection));

        return $this->getMock('Laminas\Db\Adapter\Adapter', null, array($mockHasResultDriver));
    }

    /**
     * Return a Mock object for a Db result without rows
     *
     * @return \Laminas\Db\Adapter\Adapter
     */
    protected function getMockNoResult()
    {
        // mock the adapter, driver, and parts
        $mockConnection = $this->getMock('Laminas\Db\Adapter\Driver\ConnectionInterface');

        $mockNoResult = $this->getMock('Laminas\Db\Adapter\Driver\ResultInterface');
        $mockNoResult->expects($this->any())
            ->method('current')
            ->will($this->returnValue(null));

        $mockNoResultStatement = $this->getMock('Laminas\Db\Adapter\Driver\StatementInterface');
        $mockNoResultStatement->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockNoResult));

        $mockNoResultStatement->expects($this->any())
            ->method('getParameterContainer')
            ->will($this->returnValue(new ParameterContainer()));

        $mockNoResultDriver = $this->getMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockNoResultDriver->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($mockNoResultStatement));
        $mockNoResultDriver->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($mockConnection));

        return $this->getMock('Laminas\Db\Adapter\Adapter', null, array($mockNoResultDriver));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     *
     * @return void
     */
    public function testBasicFindsRecord()
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value1'));
    }

    /**
     * Test basic function of RecordExists (no exclusion)
     *
     * @return void
     */
    public function testBasicFindsNoRecord()
    {
        $validator = new NoRecordExists('users', 'field1', null, $this->getMockNoResult());
        $this->assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     *
     * @return void
     */
    public function testExcludeWithArray()
    {
        $validator = new NoRecordExists('users', 'field1', array('field' => 'id', 'value' => 1),
                                        $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with an array
     *
     * @return void
     */
    public function testExcludeWithArrayNoRecord()
    {
        $validator = new NoRecordExists('users', 'field1', array('field' => 'id', 'value' => 1),
                                        $this->getMockNoResult());
        $this->assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test the exclusion function
     * with a string
     *
     * @return void
     */
    public function testExcludeWithString()
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value3'));
    }

    /**
     * Test the exclusion function
     * with a string
     *
     * @return void
     */
    public function testExcludeWithStringNoRecord()
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1', $this->getMockNoResult());
        $this->assertTrue($validator->isValid('nosuchvalue'));
    }

    /**
     * Test that the class throws an exception if no adapter is provided
     * and no default is set.
     *
     * @return void
     */
    public function testThrowsExceptionWithNoAdapter()
    {
        $validator = new NoRecordExists('users', 'field1', 'id != 1');
        $this->setExpectedException('Laminas\Validator\Exception\RuntimeException',
                                    'No database adapter present');
        $validator->isValid('nosuchvalue');
    }

    /**
     * Test that schemas are supported and run without error
     *
     * @return void
     */
    public function testWithSchema()
    {
        $validator = new NoRecordExists(array('table' => 'users', 'schema' => 'my'),
                                        'field1', null, $this->getMockHasResult());
        $this->assertFalse($validator->isValid('value1'));
    }

    /**
     * Test that schemas are supported and run without error
     *
     * @return void
     */
    public function testWithSchemaNoResult()
    {
        $validator = new NoRecordExists(array('table' => 'users', 'schema' => 'my'),
                                        'field1', null,  $this->getMockNoResult());
        $this->assertTrue($validator->isValid('value1'));
    }

    public function testEqualsMessageTemplates()
    {
        $validator  = new NoRecordExists('users', 'field1');
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }
}
