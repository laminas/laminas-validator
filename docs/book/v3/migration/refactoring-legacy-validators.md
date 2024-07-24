# Refactoring Legacy Validators

This document is intended to show an example of refactoring custom validators to remove runtime mutation of options and move option determination to the constructor of your validator.

## The Old Validator

The following custom validator is our starting point which relies on now removed methods and behaviour from the `AbstractValidator` in the 2.x series of releases.

```php
namespace My;

use Laminas\Validator\AbstractValidator;

class MuppetNameValidator extends AbstractValidator {
    public const KNOWN_MUPPETS = [
        'Kermit',
        'Miss Piggy',
        'Fozzie Bear',
        'Gonzo the Great',
        'Scooter',
        'Animal',
        'Beaker',
    ];
    
    public const ERR_NOT_STRING = 'notString';
    public const ERR_NOT_ALLOWED = 'notAllowed';
    
    protected array $messageTemplates = [
        self::ERR_NOT_STRING => 'Please provide a string value',
        self::ERR_NOT_ALLOWED => '"%value%" is not an allowed muppet name',
    ];
    
    public function setAllowedMuppets(array $muppets): void {
        $this->options['allowed_muppets'] = [];
        foreach ($muppets as $muppet) {
            $this->addMuppet($muppet);
        }
    }
    
    public function addMuppet(string $muppet): void
    {
        $this->options['allowed_muppets'][] = $muppet;
    }
    
    public function setCaseSensitive(bool $caseSensitive): void
    {
        $this->options['case_sensitive'] = $caseSensitive;
    }
    
    public function isValid(mixed $value): bool {
        if (! is_string($value)) {
            $this->error(self::ERR_NOT_STRING);
            
            return false;
        }
        
        $list = $this->options['allowed_muppets'];
        if (! $this->options['case_sensitive']) {
            $list = array_map('strtolower', $list);
            $value = strtolower($value);
        }
        
        if (! in_array($value, $list, true)) {
            $this->error(self::ERR_NOT_ALLOWED);
            
            return false;
        }
        
        return true;
    }
}
```

Given an array of options such as `['allowed_muppets' => ['Miss Piggy'], 'caseSensitive' => false]`, previously, the `AbstractValidator` would have "magically" called the setter methods `setAllowedMuppets` and `setCaseSensitive`. The same would be true if you provided these options to the removed `AbstractValidator::setOptions()` method.

Additionally, with the class above, there is nothing to stop you from creating the validator in an invalid state with:

```php
$validator = new MuppetNameValidator();
$validator->isValid('Kermit');
// false, because the list of allowed muppets has not been initialised
```

## The Refactored Validator

```php
final readonly class MuppetNameValidator extends AbstractValidator {
    public const KNOWN_MUPPETS = [
        'Kermit',
        'Miss Piggy',
        'Fozzie Bear',
        'Gonzo the Great',
        'Scooter',
        'Animal',
        'Beaker',
    ];
    
    public const ERR_NOT_STRING = 'notString';
    public const ERR_NOT_ALLOWED = 'notAllowed';
    
    protected array $messageTemplates = [
        self::ERR_NOT_STRING => 'Please provide a string value',
        self::ERR_NOT_ALLOWED => '"%value%" is not an allowed muppet name',
    ];
    
    private array $allowed;
    private bool $caseSensitive;
    
    /**
     * @param array{
     *     allowed_muppets: list<non-empty-string>,
     *     case_sensitive: bool,     
     * } $options
     */
    public function __construct(array $options)
    {
        $this->allowed = $options['allowed_muppets'] ?? self::KNOWN_MUPPETS;
        $this->caseSensitive = $options['case_sensitive'] ?? true;
        
        // Pass options such as the translator, overridden error messages, etc
        // to the parent AbstractValidator
        parent::__construct($options);
    }
    
    public function isValid(mixed $value): bool {
        if (! is_string($value)) {
            $this->error(self::ERR_NOT_STRING);
            
            return false;
        }
        
        $list = $this->allowed;
        if (! $this->caseSensitive) {
            $list = array_map('strtolower', $list);
            $value = strtolower($value);
        }
        
        if (! in_array($value, $list, true)) {
            $this->error(self::ERR_NOT_ALLOWED);
            
            return false;
        }
        
        return true;
    }
}
```

With the refactored validator, our options are clearly and obviously declared as class properties, and cannot be changed once they have been set.

There are fewer methods to test; In your test case you can easily set up data providers with varying options to thoroughly test that your validator behaves in the expected way.
