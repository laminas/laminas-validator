<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

use Laminas\Validator\Exception;

/**
 * Validator for the excluding file extensions
 */
class ExcludeExtension extends Extension
{
    /**
     * @const string Error constants
     */
    const FALSE_EXTENSION = 'fileExcludeExtensionFalse';
    const NOT_FOUND       = 'fileExcludeExtensionNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::FALSE_EXTENSION => "File has an incorrect extension",
        self::NOT_FOUND       => "File is not readable or does not exist",
    );

    /**
     * Returns true if and only if the file extension of $value is not included in the
     * set extension list
     *
     * @param  string|array $value Real file to check for extension
     * @return bool
     */
    public function isValid($value)
    {
        if (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
        } else {
            $file     = $value;
            $filename = basename($file);
        }
        $this->setValue($filename);

        // Is file readable ?
        if (false === stream_resolve_include_path($file)) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $extension  = substr($filename, strrpos($filename, '.') + 1);
        $extensions = $this->getExtension();

        if ($this->getCase() && (!in_array($extension, $extensions))) {
            return true;
        } elseif (!$this->getCase()) {
            foreach ($extensions as $ext) {
                if (strtolower($ext) == strtolower($extension)) {
                    $this->error(self::FALSE_EXTENSION);
                    return false;
                }
            }

            return true;
        }

        $this->error(self::FALSE_EXTENSION);
        return false;
    }
}
