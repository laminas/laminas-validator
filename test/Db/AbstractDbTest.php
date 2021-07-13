<?php

namespace LaminasTest\Validator\Db;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Sql\Select;
use Laminas\Validator\Db\AbstractDb;
use Laminas\Validator\Exception\InvalidArgumentException;
use LaminasTest\Validator\Db\TestAsset\ConcreteDbValidator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @group      Laminas_Validator
 */
class AbstractDbTest extends TestCase
{
    use ProphecyTrait;

    /** @var AbstractDb */
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new ConcreteDbValidator([
            'table'  => 'table',
            'field'  => 'field',
            'schema' => 'schema',
        ]);
    }

    public function testConstructorWithNoTableAndSchemaKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table or Schema option missing!');
        $this->validator = new ConcreteDbValidator([
            'field' => 'field',
        ]);
    }

    public function testConstructorWithNoFieldKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field option missing!');
        new ConcreteDbValidator([
            'schema' => 'schema',
            'table'  => 'table',
        ]);
    }

    public function testSetSelect(): void
    {
        $select = new Select();
        $this->validator->setSelect($select);

        $this->assertSame($select, $this->validator->getSelect());
    }

    public function testGetSchema(): void
    {
        $schema = 'test_db';
        $this->validator->setSchema($schema);

        $this->assertEquals($schema, $this->validator->getSchema());
    }

    public function testGetTable(): void
    {
        $table = 'test_table';
        $this->validator->setTable($table);

        $this->assertEquals($table, $this->validator->getTable());
    }

    public function testGetField(): void
    {
        $field = 'test_field';
        $this->validator->setField($field);

        $this->assertEquals($field, $this->validator->getField());
    }

    public function testGetExclude(): void
    {
        $field = 'test_field';
        $this->validator->setField($field);

        $this->assertEquals($field, $this->validator->getField());
    }

    /**
     * @group #46
     */
    public function testImplementationsAreDbAdapterAware(): void
    {
        $this->assertInstanceOf(AdapterAwareInterface::class, $this->validator);
    }

    /**
     * @group #46
     */
    public function testSetAdapterIsEquivalentToSetDbAdapter(): void
    {
        $adapterFirst  = $this->createStub(Adapter::class);
        $adapterSecond = $this->createStub(Adapter::class);

        $this->validator->setAdapter($adapterFirst);
        $this->assertObjectHasAttribute('adapter', $this->validator);
        $this->assertEquals($adapterFirst, $this->validator->getAdapter());

        $this->validator->setDbAdapter($adapterSecond);
        $this->assertObjectHasAttribute('adapter', $this->validator);
        $this->assertEquals($adapterSecond, $this->validator->getAdapter());
    }
}
