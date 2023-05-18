<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File\TestAsset;

use Laminas\Validator\File\FileInformationTrait;

/**
 * Validator which checks if the file already exists in the directory
 */
final class FileInformation
{
    use FileInformationTrait;

    /**
     * Returns array if the procedure is identified
     *
     * @param  string|array|object $value       Filename to check
     * @param  null|array          $file        File data (when using legacy Laminas_File_Transfer API)
     * @param  bool                $hasType     Return with filetype (optional)
     * @param  bool                $hasBasename Return with basename - is calculated from location path (optional)
     */
    public function checkFileInformation(
        $value,
        ?array $file = null,
        bool $hasType = false,
        bool $hasBasename = false
    ): array {
        return $this->getFileInfo($value, $file, $hasType, $hasBasename);
    }
}
