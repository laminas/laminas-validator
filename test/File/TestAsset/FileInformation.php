<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File\TestAsset;

use Laminas\Validator\File\FileInformationTrait;

/**
* Validator which checks if the file already exists in the directory
*/
class FileInformation
{
    use FileInformationTrait;

    /**
     * Returns array if the procedure is identified
     *
     * @param  string|array|object $value    Filename to check
     * @param  null|array          $file     File data (when using legacy Laminas_File_Transfer API)
     * @param  bool                $hasType  Return with filetype (optional)
     * @param  bool                $basename Return with basename - is calculated from location path (optional)
     * @return array
     */
    public function checkFileInformation(
        $value,
        array $file = null,
        $hasType = false,
        $hasBasename = false
    ) {
        return $this->getFileInfo($value, $file, $hasType, $hasBasename);
    }
}
