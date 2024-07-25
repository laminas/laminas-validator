# Composing `final` Validators

In version 3.0, nearly all validators have been marked as `final`.

This document aims to provide guidance on composing validators to achieve the same results as inheritance may have.

Consider the following custom validator. It ensures the value given is a valid email address and that it is an email address from `gmail.com` 

```php
namespace My;

use Laminas\Validator\EmailAddress;

class GMailOnly extends EmailAddress
{
    public const NOT_GMAIL = 'notGmail';
    
    protected $messageTemplates = [
        self::INVALID            => "Invalid type given. String expected",
        self::INVALID_FORMAT     => "The input is not a valid email address. Use the basic format local-part@hostname",
        self::INVALID_HOSTNAME   => "'%hostname%' is not a valid hostname for the email address",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have any valid MX or A records for the email address",
        self::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network",
        self::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
        self::QUOTED_STRING      => "'%localPart%' can not be matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for the email address",
        self::LENGTH_EXCEEDED    => "The input exceeds the allowed length",
        // And the one new constant introduced:
        self::NOT_GMAIL => 'Please use a gmail address',
    ]; 
    
    public function isValid(mixed $value) : bool
    {
        if (! parent::isValid($value)) {
            return false;
        }
        
        if (! preg_match('/@gmail\.com$/', $value)) {
            $this->error(self::NOT_GMAIL);
            
            return false;
        }
        
        return true;
    }
}
```

A better approach could be to use a validator chain:

```php
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorChain;

$chain = new ValidatorChain();
$chain->attachByName(EmailAddress::class);
$chain->attachByName(Regex::class, [
    'pattern' => '/@gmail\.com$/',
    'messages' => [
        Regex::NOT_MATCH => 'Please use a gmail.com address',
    ],
]);
```

Or, to compose the email validator into a concrete class:

```php
namespace My;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\EmailAddress;

final class GMailOnly extends AbstractValidator
{
    public const NOT_GMAIL = 'notGmail';
    public const INVALID = 'invalid';
    
    protected $messageTemplates = [
        self::INVALID   => 'Please provide a valid email address',
        self::NOT_GMAIL => 'Please use a gmail address',
    ];
    
    public function __construct(
        private readonly EmailAddress $emailValidator
    ) {
    }
    
    public function isValid(mixed $value) : bool
    {
        if (! $this->emailValidator->isValid($value)) {
            $this->error(self::INVALID);
            
            return false;
        }
        
        if (strtoupper($value) !== $value) {
            $this->error(self::NOT_GMAIL);
            
            return false;
        }
        
        return true;
    }
}
```

In the latter case you would need to define factory for your validator which for this contrived example would seem like overkill, but for more real-world use cases a factory is likely employed already:

```php
use Laminas\Validator\EmailAddress;
use Laminas\Validator\ValidatorPluginManager;
use Psr\Container\ContainerInterface;

final class GMailOnlyFactory {
    public function __invoke(ContainerInterface $container, string $name, array $options = []): GMailOnly
    {
        $pluginManager = $container->get(ValidatorPluginManager::class);
        
        return new GmailOnly(
            $pluginManager->build(EmailAddress::class, $options),
        );
    }
}
```
