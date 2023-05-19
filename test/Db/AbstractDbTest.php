<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Db;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Sql\Select;
use Laminas\Validator\Exception\InvalidArgumentException;
use LaminasTest\Validator\Db\TestAsset\ConcreteDbValidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class AbstractDbTest extends TestCase
{
    private ConcreteDbValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

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

        self::assertSame($select, $this->validator->getSelect());
    }

    public function testGetSchema(): void
    {
        $schema = 'test_db';
        $this->validator->setSchema($schema);

        self::assertSame($schema, $this->validator->getSchema());
    }

    public function testGetTable(): void
    {
        $table = 'test_table';
        $this->validator->setTable($table);

        self::assertSame($table, $this->validator->getTable());
    }

    public function testGetField(): void
    {
        $field = 'test_field';
        $this->validator->setField($field);

        self::assertSame($field, $this->validator->getField());
    }

    public function testGetExclude(): void
    {
        $field = 'test_field';
        $this->validator->setField($field);

        self::assertSame($field, $this->validator->getField());
    }

    #[Group('#46')]
    public function testImplementationsAreDbAdapterAware(): void
    {
        self::assertInstanceOf(AdapterAwareInterface::class, $this->validator);
    }

    #[Group('#46')]
    public function testSetAdapterIsEquivalentToSetDbAdapter(): void
    {
        $adapterFirst  = $this->createStub(Adapter::class);
        $adapterSecond = $this->createStub(Adapter::class);

        $this->validator->setAdapter($adapterFirst);
        self::assertEquals($adapterFirst, $this->validator->getAdapter());

        $this->validator->setDbAdapter($adapterSecond);
        self::assertEquals($adapterSecond, $this->validator->getAdapter());
    }
}
