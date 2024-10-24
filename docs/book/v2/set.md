# Standard Validation Classes

The following validators come with the laminas-validator distribution.

- [Barcode](validators/barcode.md)
- [Between](validators/between.md)
- [Callback](validators/callback.md)
- [CreditCard](validators/credit-card.md)
- [CSRF (Cross-site request forgery](validators/csrf.md)
- [Date](validators/date.md)
- [DateComparison](validators/date-comparison.md)
- [RecordExists and NoRecordExists (database)](validators/db.md)
- [Digits](validators/digits.md)
- [EmailAddress](validators/email-address.md)
- [Explode](validators/explode.md)
- [File Validation Classes](validators/file/intro.md)
- [GreaterThan](validators/greater-than.md)
- [Hex](validators/hex.md)
- [Hostname](validators/hostname.md)
- [HostWithPublicIPv4Address](validators/host-with-public-ipv4-address.md)
- [Iban](validators/iban.md)
- [Identical](validators/identical.md)
- [InArray](validators/in-array.md)
- [Ip](validators/ip.md)
- [IsArray](validators/is-array.md)
- [Isbn](validators/isbn.md)
- [IsCountable](validators/is-countable.md)
- [IsInstanceOf](validators/isinstanceof.md)
- [LessThan](validators/less-than.md)
- [NotEmpty](validators/not-empty.md)
- [NumberComparison](validators/number-comparison.md)
- [Regex](validators/regex.md)
- [Sitemap](validators/sitemap.md)
- [Step](validators/step.md)
- [StringLength](validators/string-length.md)
- [Timezone](validators/timezone.md)
- [Uri](validators/uri.md)
- [Uuid](validators/uuid.md)

## Additional validators

Several other components offer validators as well:

- [laminas-i18n](https://docs.laminas.dev/laminas-i18n/validators/)

## Deprecated Validators

### Ccnum

The `Ccnum` validator has been deprecated in favor of the `CreditCard`
validator. For security reasons you should use `CreditCard` instead of `Ccnum`.
