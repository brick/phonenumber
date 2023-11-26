# Changelog

## [0.6.0](https://github.com/brick/phonenumber/releases/tag/0.6.0) - 2023-11-26

💥 **BC breaks**

- Minimum PHP version is now `8.1`
- `PhoneNumberType` and `PhoneNumberFormat` are now native PHP enums

## [0.5.0](https://github.com/brick/phonenumber/releases/tag/0.5.0) - 2023-02-23

**💥 BC breaks**

- Minimum PHP version is now `7.4`

## [0.4.1](https://github.com/brick/phonenumber/releases/tag/0.4.1) - 2023-02-22

**✨ Improvements**

- Static analysis: Psalm & PHPStan -compatible annotations for `PhoneNumberType` and `PhoneNumberFormat` constants

## [0.4.0](https://github.com/brick/phonenumber/releases/tag/0.4.0) - 2021-09-06

✨ **New features**

- `PhoneNumber::getDescription()`

💥 **BC breaks**

- The following classes are now `final`:
    - `PhoneNumber`
    - `PhoneNumberFormat`
    - `PhoneNumberParseException`
    - `PhoneNumberType`

## [0.3.0](https://github.com/brick/phonenumber/releases/tag/0.3.0) - 2021-08-05

✨ **New features**

- `PhoneNumber::isEqualTo()`
- `PhoneNumber` now implements `JsonSerializable`

💥 **BC breaks**

- The library now requires the `json` extension (always available as of PHP 8.0)

## [0.2.2](https://github.com/brick/phonenumber/releases/tag/0.2.2) - 2020-02-06

✨ **New method:** `PhoneNumber::getGeographicalAreaCode()`

## [0.2.1](https://github.com/brick/phonenumber/releases/tag/0.2.1) - 2018-11-13

New method: `PhoneNumber::isPossibleNumber()`

Thanks @xificurk

## [0.2.0](https://github.com/brick/phonenumber/releases/tag/0.2.0) - 2017-10-04

Minimum PHP version is now 7.1.

## [0.1.0](https://github.com/brick/phonenumber/releases/tag/0.1.0) - 2017-04-05

First beta release.

