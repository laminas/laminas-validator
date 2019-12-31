<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db;

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
        $this->validator = new ConcreteDbValidator([
            'table' => 'table',
            'field' => 'field',
            'schema' => 'schema',
        ]);
    }

    public function testConstructorWithNoTableAndSchemaKey()
    {
        $this->setExpectedException('Laminas\Validator\Exception\InvalidArgumentException',
        'Table or Schema option missing!');
        $this->validator = new ConcreteDbValidator([
            'field' => 'field',
        ]);
    }

    public function testConstructorWithNoFieldKey()
    {
        $this->setExpectedException('Laminas\Validator\Exception\InvalidArgumentException',
        'Field option missing!');
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
}
