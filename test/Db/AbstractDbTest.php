<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Sql\Select;
use LaminasTest\Validator\Db\TestAsset\ConcreteDbValidator;

/**
 * @group      Laminas_Validator
 */
class AbstractDbTest extends \PHPUnit_Framework_TestCase
{

    protected $validator;

    public function setUp()
    {
        if (! class_exists('Laminas\Db\Adapter\Adapter')) {
            $this->markTestSkipped(
                'Skipping laminas-db-related tests until that component is updated '
                . 'to laminas-servicemanager/laminas-eventmanager v3'
            );
        }

        $this->validator = new ConcreteDbValidator([
            'table' => 'table',
            'field' => 'field',
            'schema' => 'schema',
        ]);
    }

    public function testConstructorWithNoTableAndSchemaKey()
    {
        $this->setExpectedException(
            'Laminas\Validator\Exception\InvalidArgumentException',
            'Table or Schema option missing!'
        );
        $this->validator = new ConcreteDbValidator([
            'field' => 'field',
        ]);
    }

    public function testConstructorWithNoFieldKey()
    {
        $this->setExpectedException(
            'Laminas\Validator\Exception\InvalidArgumentException',
            'Field option missing!'
        );
        $validator = new ConcreteDbValidator([
            'schema' => 'schema',
            'table' => 'table',
        ]);
    }

    public function testSetSelect()
    {
        $select = new Select();
        $this->validator->setSelect($select);

        $this->assertSame($select, $this->validator->getSelect());
    }

    public function testGetSchema()
    {
        $schema = 'test_db';
        $this->validator->setSchema($schema);

        $this->assertEquals($schema, $this->validator->getSchema());
    }

    public function testGetTable()
    {
        $table = 'test_table';
        $this->validator->setTable($table);

        $this->assertEquals($table, $this->validator->getTable());
    }

    public function testGetField()
    {
        $field = 'test_field';
        $this->validator->setField($field);

        $this->assertEquals($field, $this->validator->getField());
    }

    public function testGetExclude()
    {
        $field = 'test_field';
        $this->validator->setField($field);

        $this->assertEquals($field, $this->validator->getField());
    }

    /**
     * @group #46
     */
    public function testImplementationsAreDbAdapterAware()
    {
        $this->assertInstanceOf(AdapterAwareInterface::class, $this->validator);
    }

    /**
     * @group #46
     */
    public function testSetAdapterIsEquivalentToSetDbAdapter()
    {
        $adapterFirst = $this->prophesize(Adapter::class)->reveal();
        $adapterSecond = $this->prophesize(Adapter::class)->reveal();

        $this->validator->setAdapter($adapterFirst);
        $this->assertAttributeSame($adapterFirst, 'adapter', $this->validator);

        $this->validator->setDbAdapter($adapterSecond);
        $this->assertAttributeSame($adapterSecond, 'adapter', $this->validator);
    }
}
