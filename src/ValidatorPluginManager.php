<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;

/**
 * @category   Laminas
 * @package    Laminas_Validator
 */
class ValidatorPluginManager extends AbstractPluginManager
{
    /**
     * Default set of validators
     *
     * @var array
     */
    protected $invokableClasses = array(
        'alnum'                    => 'Laminas\I18n\Validator\Alnum',
        'alpha'                    => 'Laminas\I18n\Validator\Alpha',
        'barcodecode25interleaved' => 'Laminas\Validator\Barcode\Code25interleaved',
        'barcodecode25'            => 'Laminas\Validator\Barcode\Code25',
        'barcodecode39ext'         => 'Laminas\Validator\Barcode\Code39ext',
        'barcodecode39'            => 'Laminas\Validator\Barcode\Code39',
        'barcodecode93ext'         => 'Laminas\Validator\Barcode\Code93ext',
        'barcodecode93'            => 'Laminas\Validator\Barcode\Code93',
        'barcodeean12'             => 'Laminas\Validator\Barcode\Ean12',
        'barcodeean13'             => 'Laminas\Validator\Barcode\Ean13',
        'barcodeean14'             => 'Laminas\Validator\Barcode\Ean14',
        'barcodeean18'             => 'Laminas\Validator\Barcode\Ean18',
        'barcodeean2'              => 'Laminas\Validator\Barcode\Ean2',
        'barcodeean5'              => 'Laminas\Validator\Barcode\Ean5',
        'barcodeean8'              => 'Laminas\Validator\Barcode\Ean8',
        'barcodegtin12'            => 'Laminas\Validator\Barcode\Gtin12',
        'barcodegtin13'            => 'Laminas\Validator\Barcode\Gtin13',
        'barcodegtin14'            => 'Laminas\Validator\Barcode\Gtin14',
        'barcodeidentcode'         => 'Laminas\Validator\Barcode\Identcode',
        'barcodeintelligentmail'   => 'Laminas\Validator\Barcode\Intelligentmail',
        'barcodeissn'              => 'Laminas\Validator\Barcode\Issn',
        'barcodeitf14'             => 'Laminas\Validator\Barcode\Itf14',
        'barcodeleitcode'          => 'Laminas\Validator\Barcode\Leitcode',
        'barcodeplanet'            => 'Laminas\Validator\Barcode\Planet',
        'barcodepostnet'           => 'Laminas\Validator\Barcode\Postnet',
        'barcoderoyalmail'         => 'Laminas\Validator\Barcode\Royalmail',
        'barcodesscc'              => 'Laminas\Validator\Barcode\Sscc',
        'barcodeupca'              => 'Laminas\Validator\Barcode\Upca',
        'barcodeupce'              => 'Laminas\Validator\Barcode\Upce',
        'barcode'                  => 'Laminas\Validator\Barcode',
        'between'                  => 'Laminas\Validator\Between',
        'callback'                 => 'Laminas\Validator\Callback',
        'creditcard'               => 'Laminas\Validator\CreditCard',
        'csrf'                     => 'Laminas\Validator\Csrf',
        'date'                     => 'Laminas\Validator\Date',
        'datestep'                 => 'Laminas\Validator\DateStep',
        'dbnorecordexists'         => 'Laminas\Validator\Db\NoRecordExists',
        'dbrecordexists'           => 'Laminas\Validator\Db\RecordExists',
        'digits'                   => 'Laminas\Validator\Digits',
        'emailaddress'             => 'Laminas\Validator\EmailAddress',
        'explode'                  => 'Laminas\Validator\Explode',
        'filecount'                => 'Laminas\Validator\File\Count',
        'filecrc32'                => 'Laminas\Validator\File\Crc32',
        'fileexcludeextension'     => 'Laminas\Validator\File\ExcludeExtension',
        'fileexcludemimetype'      => 'Laminas\Validator\File\ExcludeMimeType',
        'fileexists'               => 'Laminas\Validator\File\Exists',
        'fileextension'            => 'Laminas\Validator\File\Extension',
        'filefilessize'            => 'Laminas\Validator\File\FilesSize',
        'filehash'                 => 'Laminas\Validator\File\Hash',
        'fileimagesize'            => 'Laminas\Validator\File\ImageSize',
        'fileiscompressed'         => 'Laminas\Validator\File\IsCompressed',
        'fileisimage'              => 'Laminas\Validator\File\IsImage',
        'filemd5'                  => 'Laminas\Validator\File\Md5',
        'filemimetype'             => 'Laminas\Validator\File\MimeType',
        'filenotexists'            => 'Laminas\Validator\File\NotExists',
        'filesha1'                 => 'Laminas\Validator\File\Sha1',
        'filesize'                 => 'Laminas\Validator\File\Size',
        'fileupload'               => 'Laminas\Validator\File\Upload',
        'filewordcount'            => 'Laminas\Validator\File\WordCount',
        'float'                    => 'Laminas\I18n\Validator\Float',
        'greaterthan'              => 'Laminas\Validator\GreaterThan',
        'hex'                      => 'Laminas\Validator\Hex',
        'hostname'                 => 'Laminas\Validator\Hostname',
        'iban'                     => 'Laminas\I18n\Validator\Iban',
        'identical'                => 'Laminas\Validator\Identical',
        'inarray'                  => 'Laminas\Validator\InArray',
        'int'                      => 'Laminas\I18n\Validator\Int',
        'ip'                       => 'Laminas\Validator\Ip',
        'isbn'                     => 'Laminas\Validator\Isbn',
        'lessthan'                 => 'Laminas\Validator\LessThan',
        'notempty'                 => 'Laminas\Validator\NotEmpty',
        'postcode'                 => 'Laminas\I18n\Validator\PostCode',
        'regex'                    => 'Laminas\Validator\Regex',
        'sitemapchangefreq'        => 'Laminas\Validator\Sitemap\Changefreq',
        'sitemaplastmod'           => 'Laminas\Validator\Sitemap\Lastmod',
        'sitemaploc'               => 'Laminas\Validator\Sitemap\Loc',
        'sitemappriority'          => 'Laminas\Validator\Sitemap\Priority',
        'stringlength'             => 'Laminas\Validator\StringLength',
        'step'                     => 'Laminas\Validator\Step',
        'uri'                      => 'Laminas\Validator\Uri',
    );

    /**
     * Whether or not to share by default; default to false
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached translator, if any, to the currently requested helper.
     *
     * @param  null|ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
        $this->addInitializer(array($this, 'injectTranslator'));
    }

    /**
     * Inject a validator instance with the registered translator
     *
     * @param  ValidatorInterface $validator
     * @return void
     */
    public function injectTranslator($validator)
    {
        if ($validator instanceof TranslatorAwareInterface) {
            $locator = $this->getServiceLocator();
            if ($locator && $locator->has('translator')) {
                $validator->setTranslator($locator->get('translator'));
            }
        }
    }

    /**
     * Validate the plugin
     *
     * Checks that the validator loaded is an instance of ValidatorInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ValidatorInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\ValidatorInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
