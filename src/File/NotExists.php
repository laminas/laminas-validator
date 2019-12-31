<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

/**
 * Validator which checks if the destination file does not exist
 *
 * @category  Laminas
 * @package   Laminas_Validator
 */
class NotExists extends Exists
{
    /**
     * @const string Error constants
     */
    const DOES_EXIST = 'fileNotExistsDoesExist';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_EXIST => "File '%value%' exists",
    );

    /**
     * Returns true if and only if the file does not exist in the set destinations
     *
     * @param  string  $value Real file to check for
     * @param  array   $file  File data from \Laminas\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        $directories = $this->getDirectory(true);
        if (($file !== null) && (!empty($file['destination']))) {
            $directories[] = $file['destination'];
        } elseif (!isset($file['name'])) {
            $file['name'] = $value;
        }

        foreach ($directories as $directory) {
            if (empty($directory)) {
                continue;
            }

            $check = true;
            if (file_exists($directory . DIRECTORY_SEPARATOR . $file['name'])) {
                return $this->throwError($file, self::DOES_EXIST);
            }
        }

        if (!isset($check)) {
            return $this->throwError($file, self::DOES_EXIST);
        }

        return true;
    }
}
