<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function get_debug_type;
use function method_exists;
use function sprintf;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @extends AbstractPluginManager<ValidatorInterface>
 */
final class ValidatorPluginManager extends AbstractPluginManager
{
    /**
     * Default set of aliases
     *
     * @inheritDoc
     */
    protected $aliases = [
        'barcode'                => Barcode::class,
        'Barcode'                => Barcode::class,
        'BIC'                    => BusinessIdentifierCode::class,
        'bic'                    => BusinessIdentifierCode::class,
        'bitwise'                => Bitwise::class,
        'Bitwise'                => Bitwise::class,
        'BusinessIdentifierCode' => BusinessIdentifierCode::class,
        'businessidentifiercode' => BusinessIdentifierCode::class,
        'callback'               => Callback::class,
        'Callback'               => Callback::class,
        'creditcard'             => CreditCard::class,
        'creditCard'             => CreditCard::class,
        'CreditCard'             => CreditCard::class,
        'date'                   => Date::class,
        'Date'                   => Date::class,
        'datestep'               => DateStep::class,
        'dateStep'               => DateStep::class,
        'DateStep'               => DateStep::class,
        'digits'                 => Digits::class,
        'Digits'                 => Digits::class,
        'emailaddress'           => EmailAddress::class,
        'emailAddress'           => EmailAddress::class,
        'EmailAddress'           => EmailAddress::class,
        'explode'                => Explode::class,
        'Explode'                => Explode::class,
        'filecount'              => File\Count::class,
        'fileCount'              => File\Count::class,
        'FileCount'              => File\Count::class,
        'filecrc32'              => File\Crc32::class,
        'fileCrc32'              => File\Crc32::class,
        'FileCrc32'              => File\Crc32::class,
        'fileexcludeextension'   => File\ExcludeExtension::class,
        'fileExcludeExtension'   => File\ExcludeExtension::class,
        'FileExcludeExtension'   => File\ExcludeExtension::class,
        'fileexcludemimetype'    => File\ExcludeMimeType::class,
        'fileExcludeMimeType'    => File\ExcludeMimeType::class,
        'FileExcludeMimeType'    => File\ExcludeMimeType::class,
        'fileexists'             => File\Exists::class,
        'fileExists'             => File\Exists::class,
        'FileExists'             => File\Exists::class,
        'fileextension'          => File\Extension::class,
        'fileExtension'          => File\Extension::class,
        'FileExtension'          => File\Extension::class,
        'filefilessize'          => File\FilesSize::class,
        'fileFilesSize'          => File\FilesSize::class,
        'FileFilesSize'          => File\FilesSize::class,
        'filehash'               => File\Hash::class,
        'fileHash'               => File\Hash::class,
        'FileHash'               => File\Hash::class,
        'fileimagesize'          => File\ImageSize::class,
        'fileImageSize'          => File\ImageSize::class,
        'FileImageSize'          => File\ImageSize::class,
        'fileiscompressed'       => File\IsCompressed::class,
        'fileIsCompressed'       => File\IsCompressed::class,
        'FileIsCompressed'       => File\IsCompressed::class,
        'fileisimage'            => File\IsImage::class,
        'fileIsImage'            => File\IsImage::class,
        'FileIsImage'            => File\IsImage::class,
        'filemd5'                => File\Md5::class,
        'fileMd5'                => File\Md5::class,
        'FileMd5'                => File\Md5::class,
        'filemimetype'           => File\MimeType::class,
        'fileMimeType'           => File\MimeType::class,
        'FileMimeType'           => File\MimeType::class,
        'filenotexists'          => File\NotExists::class,
        'fileNotExists'          => File\NotExists::class,
        'FileNotExists'          => File\NotExists::class,
        'filesha1'               => File\Sha1::class,
        'fileSha1'               => File\Sha1::class,
        'FileSha1'               => File\Sha1::class,
        'filesize'               => File\Size::class,
        'fileSize'               => File\Size::class,
        'FileSize'               => File\Size::class,
        'fileupload'             => File\Upload::class,
        'fileUpload'             => File\Upload::class,
        'FileUpload'             => File\Upload::class,
        'fileuploadfile'         => File\UploadFile::class,
        'fileUploadFile'         => File\UploadFile::class,
        'FileUploadFile'         => File\UploadFile::class,
        'filewordcount'          => File\WordCount::class,
        'fileWordCount'          => File\WordCount::class,
        'FileWordCount'          => File\WordCount::class,
        'gpspoint'               => GpsPoint::class,
        'gpsPoint'               => GpsPoint::class,
        'GpsPoint'               => GpsPoint::class,
        'hex'                    => Hex::class,
        'Hex'                    => Hex::class,
        'hostname'               => Hostname::class,
        'Hostname'               => Hostname::class,
        'iban'                   => Iban::class,
        'Iban'                   => Iban::class,
        'identical'              => Identical::class,
        'Identical'              => Identical::class,
        'inarray'                => InArray::class,
        'inArray'                => InArray::class,
        'InArray'                => InArray::class,
        'ip'                     => Ip::class,
        'Ip'                     => Ip::class,
        'IsArray'                => IsArray::class,
        'isbn'                   => Isbn::class,
        'Isbn'                   => Isbn::class,
        'isCountable'            => IsCountable::class,
        'IsCountable'            => IsCountable::class,
        'iscountable'            => IsCountable::class,
        'isinstanceof'           => IsInstanceOf::class,
        'isInstanceOf'           => IsInstanceOf::class,
        'IsInstanceOf'           => IsInstanceOf::class,
        'notempty'               => NotEmpty::class,
        'notEmpty'               => NotEmpty::class,
        'NotEmpty'               => NotEmpty::class,
        'regex'                  => Regex::class,
        'Regex'                  => Regex::class,
        'sitemapchangefreq'      => Sitemap\Changefreq::class,
        'sitemapChangefreq'      => Sitemap\Changefreq::class,
        'SitemapChangefreq'      => Sitemap\Changefreq::class,
        'sitemaplastmod'         => Sitemap\Lastmod::class,
        'sitemapLastmod'         => Sitemap\Lastmod::class,
        'SitemapLastmod'         => Sitemap\Lastmod::class,
        'sitemaploc'             => Sitemap\Loc::class,
        'sitemapLoc'             => Sitemap\Loc::class,
        'SitemapLoc'             => Sitemap\Loc::class,
        'sitemappriority'        => Sitemap\Priority::class,
        'sitemapPriority'        => Sitemap\Priority::class,
        'SitemapPriority'        => Sitemap\Priority::class,
        'stringlength'           => StringLength::class,
        'stringLength'           => StringLength::class,
        'StringLength'           => StringLength::class,
        'step'                   => Step::class,
        'Step'                   => Step::class,
        'timezone'               => Timezone::class,
        'Timezone'               => Timezone::class,
        'uri'                    => Uri::class,
        'Uri'                    => Uri::class,
        'uuid'                   => Uuid::class,
        'Uuid'                   => Uuid::class,
    ];

    /**
     * Default set of factories
     *
     * @inheritDoc
     */
    protected $factories = [
        Barcode::class                   => InvokableFactory::class,
        Bitwise::class                   => InvokableFactory::class,
        BusinessIdentifierCode::class    => InvokableFactory::class,
        Callback::class                  => InvokableFactory::class,
        CreditCard::class                => InvokableFactory::class,
        DateStep::class                  => InvokableFactory::class,
        Date::class                      => InvokableFactory::class,
        Digits::class                    => InvokableFactory::class,
        EmailAddress::class              => InvokableFactory::class,
        Explode::class                   => InvokableFactory::class,
        File\Count::class                => InvokableFactory::class,
        File\Crc32::class                => InvokableFactory::class,
        File\ExcludeExtension::class     => InvokableFactory::class,
        File\ExcludeMimeType::class      => InvokableFactory::class,
        File\Exists::class               => InvokableFactory::class,
        File\Extension::class            => InvokableFactory::class,
        File\FilesSize::class            => InvokableFactory::class,
        File\Hash::class                 => InvokableFactory::class,
        File\ImageSize::class            => InvokableFactory::class,
        File\IsCompressed::class         => InvokableFactory::class,
        File\IsImage::class              => InvokableFactory::class,
        File\Md5::class                  => InvokableFactory::class,
        File\MimeType::class             => InvokableFactory::class,
        File\NotExists::class            => InvokableFactory::class,
        File\Sha1::class                 => InvokableFactory::class,
        File\Size::class                 => InvokableFactory::class,
        File\Upload::class               => InvokableFactory::class,
        File\UploadFile::class           => InvokableFactory::class,
        File\WordCount::class            => InvokableFactory::class,
        GpsPoint::class                  => InvokableFactory::class,
        Hex::class                       => InvokableFactory::class,
        Hostname::class                  => InvokableFactory::class,
        HostWithPublicIPv4Address::class => InvokableFactory::class,
        Iban::class                      => InvokableFactory::class,
        Identical::class                 => InvokableFactory::class,
        InArray::class                   => InvokableFactory::class,
        Ip::class                        => InvokableFactory::class,
        IsArray::class                   => InvokableFactory::class,
        Isbn::class                      => InvokableFactory::class,
        IsCountable::class               => InvokableFactory::class,
        IsInstanceOf::class              => InvokableFactory::class,
        IsJsonString::class              => InvokableFactory::class,
        NotEmpty::class                  => InvokableFactory::class,
        Regex::class                     => InvokableFactory::class,
        Sitemap\Changefreq::class        => InvokableFactory::class,
        Sitemap\Lastmod::class           => InvokableFactory::class,
        Sitemap\Loc::class               => InvokableFactory::class,
        Sitemap\Priority::class          => InvokableFactory::class,
        StringLength::class              => InvokableFactory::class,
        Step::class                      => InvokableFactory::class,
        Timezone::class                  => InvokableFactory::class,
        Uri::class                       => InvokableFactory::class,
        Uuid::class                      => InvokableFactory::class,
    ];

    /**
     * Whether or not to share by default; default to false (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Whether or not to share by default; default to false (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Default instance type
     *
     * @inheritDoc
     */
    protected $instanceOf = ValidatorInterface::class;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached translator, if any, to the currently requested helper.
     *
     * {@inheritDoc}
     *
     * @param ServiceManagerConfiguration $v3config
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);

        $this->addInitializer([$this, 'injectTranslator']);
        $this->addInitializer([$this, 'injectValidatorPluginManager']);
    }

    /**
     * @param mixed $instance
     * @psalm-assert ValidatorInterface $instance
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s expects only to create instances of %s; %s is invalid',
                static::class,
                (string) $this->instanceOf,
                get_debug_type($instance)
            ));
        }
    }

    /**
     * For v2 compatibility: validate plugin instance.
     *
     * Proxies to `validate()`.
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    public function validatePlugin(mixed $plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                get_debug_type($plugin),
                ValidatorInterface::class
            ), $e->getCode(), $e);
        }
    }

    /**
     * Inject a validator instance with the registered translator
     *
     * @param  ContainerInterface|object $first
     * @param  ContainerInterface|object $second
     * @return void
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $validator = $second;
        } else {
            $container = $second;
            $validator = $first;
        }

        if (! $validator instanceof Translator\TranslatorAwareInterface) {
            return;
        }

        // V2 means we pull it from the parent container
        if ($container === $this && method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        if (! $container instanceof ContainerInterface) {
            return;
        }

        if ($container->has('MvcTranslator')) {
            $validator->setTranslator($container->get('MvcTranslator'));

            return;
        }

        if ($container->has(TranslatorInterface::class)) {
            $validator->setTranslator($container->get(Translator\TranslatorInterface::class));
        }
    }

    /**
     * Inject a validator plugin manager
     *
     * @param  ContainerInterface|object $first
     * @param  ContainerInterface|object $second
     * @return void
     */
    public function injectValidatorPluginManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $validator = $second;
        } else {
            $validator = $first;
        }
        if ($validator instanceof ValidatorPluginManagerAwareInterface) {
            $validator->setValidatorPluginManager($this);
        }
    }
}
