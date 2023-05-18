<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Md5;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class Md5Test extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string|string[],
     *     1: string|array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool,
     *     3: string
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            [
                'ed74c22109fe9f110579f77b053b8bc3',
                $testFile,
                true,
                '',
            ],
            [
                '4d74c22109fe9f110579f77b053b8bc3',
                $testFile,
                false,
                'fileMd5DoesNotMatch',
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', 'ed74c22109fe9f110579f77b053b8bc3'],
                $testFile,
                true,
                '',
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', '7d74c22109fe9f110579f77b053b8bc3'],
                $testFile,
                false,
                'fileMd5DoesNotMatch',
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ed74c22109fe9f110579f77b053b8bc3', $testFile, false, 'fileMd5NotFound'],
        ];

        $testFile      = __DIR__ . '/_files/testsize.mo';
        $sizeFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ec441f84a2944405baa22873cda22370', $testFile, true,  ''],
            ['7d74c22109fe9f110579f77b053b8bc3', $testFile, false, 'fileMd5DoesNotMatch'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $sizeFileTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => 0,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new Md5($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new Md5($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that getMd5() returns expected value
     */
    public function testGetMd5(): void
    {
        $validator = new Md5('12345');
        self::assertSame(['12345' => 'md5'], $validator->getMd5());

        $validator = new Md5(['12345', '12333', '12344']);
        self::assertSame(['12345' => 'md5', '12333' => 'md5', '12344' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that getHash() returns expected value
     */
    public function testGetHash(): void
    {
        $validator = new Md5('12345');
        self::assertSame(['12345' => 'md5'], $validator->getHash());

        $validator = new Md5(['12345', '12333', '12344']);
        self::assertSame(['12345' => 'md5', '12333' => 'md5', '12344' => 'md5'], $validator->getHash());
    }

    /**
     * Ensures that setMd5() returns expected value
     */
    public function testSetMd5(): void
    {
        $validator = new Md5('12345');
        $validator->setMd5('12333');

        self::assertSame(['12333' => 'md5'], $validator->getMd5());

        $validator->setMd5(['12321', '12121']);

        self::assertSame(['12321' => 'md5', '12121' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that setHash() returns expected value
     */
    public function testSetHash(): void
    {
        $validator = new Md5('12345');
        $validator->setHash('12333');

        self::assertSame(['12333' => 'md5'], $validator->getMd5());

        $validator->setHash(['12321', '12121']);

        self::assertSame(['12321' => 'md5', '12121' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that addMd5() returns expected value
     */
    public function testAddMd5(): void
    {
        $validator = new Md5('12345');
        $validator->addMd5('12344');

        self::assertSame(['12345' => 'md5', '12344' => 'md5'], $validator->getMd5());

        $validator->addMd5(['12321', '12121']);

        self::assertSame(
            ['12345' => 'md5', '12344' => 'md5', '12321' => 'md5', '12121' => 'md5'],
            $validator->getMd5()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new Md5('12345');
        $validator->addHash('12344');

        self::assertSame(['12345' => 'md5', '12344' => 'md5'], $validator->getMd5());

        $validator->addHash(['12321', '12121']);

        self::assertSame(
            ['12345' => 'md5', '12344' => 'md5', '12321' => 'md5', '12121' => 'md5'],
            $validator->getMd5()
        );
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Md5('12345');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileMd5NotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Md5();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Md5::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Md5::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new Md5();
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);

        $validator->isValid($value);
    }
}
