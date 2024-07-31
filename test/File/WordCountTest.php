<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\WordCount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function current;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from WordCount */
final class WordCountTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile = __DIR__ . '/_files/wordcount.txt';
        $testData = [
            //    Options, isValid Param, Expected value
            [['max' => 15], $testFile, true],
            [['max' => '15'], $testFile, true],
            [['max' => 4], $testFile, false],
            [['min' => 0, 'max' => 10], $testFile, true],
            [['min' => 10, 'max' => 15], $testFile, false],
        ];

        // Dupe data in File Upload format
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $testFile,
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => UPLOAD_ERR_OK,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2]];

            $psr = new UploadedFile(
                $testFile,
                200,
                UPLOAD_ERR_OK,
                'foo.txt',
                'text/plain',
            );

            $testData[] = [$data[0], $psr, $data[2]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $value, bool $expected): void
    {
        $validator = new WordCount($options);

        self::assertSame($expected, $validator->isValid($value));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new WordCount(['min' => 1, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey(WordCount::NOT_FOUND, $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new WordCount(['min' => 1]);

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(WordCount::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(WordCount::NOT_FOUND, $validator->getMessages());
    }

    public function testNoOptionsCausesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A minimum or maximum word count must be set');

        new WordCount([]);
    }

    public function testInvalidMinMAxRangeCausesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum word count should be less than the maximum word count');

        new WordCount(['min' => 10, 'max' => 5]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new WordCount(['min' => 1]);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(WordCount::NOT_FOUND, $messages);
        } finally {
            unlink($path);
        }
    }
}
