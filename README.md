# Brick\PhoneNumber

<img src="https://raw.githubusercontent.com/brick/brick/master/logo.png" alt="" align="left" height="64">

A phone number library for PHP.

[![Build Status](https://github.com/brick/phonenumber/workflows/CI/badge.svg)](https://github.com/brick/phonenumber/actions)
[![Coverage Status](https://coveralls.io/repos/github/brick/phonenumber/badge.svg?branch=master)](https://coveralls.io/github/brick/phonenumber?branch=master)
[![Latest Stable Version](https://poser.pugx.org/brick/phonenumber/v/stable)](https://packagist.org/packages/brick/phonenumber)
[![Total Downloads](https://poser.pugx.org/brick/phonenumber/downloads)](https://packagist.org/packages/brick/phonenumber)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

This library is a thin wrapper around [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php),
itself a port of [Google's libphonenumber](https://github.com/googlei18n/libphonenumber).

It provides an equivalent functionality, with the following implementation differences:

- `PhoneNumber` is an immutable class; it can be safely passed around without having to worry about the risk for it to be changed;
- `PhoneNumber` is not just a mere data container, but provides all the methods to parse, format and validate phone numbers; it transparently encapsulates `PhoneNumberUtil`.

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require brick/phonenumber
```

## Requirements

This library requires PHP 7.1 or later. for PHP 5.6 and PHP 7.0 support, use version `0.1`.

## Project status & release process

While this library is still under development, it is well tested and should be stable enough to use in production environments.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.4.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/brick/phonenumber/releases) for a list of changes introduced by each further `0.x.0` version.


## Quick start

All the classes lie in the `Brick\PhoneNumber` namespace.

To obtain an instance of `PhoneNumber`, use the `parse()` method:

- Using an international number: `PhoneNumber::parse('+33123456789')`;
- Using a national number and a country code: `PhoneNumber::parse('01 23 45 67 89', 'FR')`;

### Validating a number

The `parse()` method is quite permissive with numbers; it basically attempts to match a country code,
and validates the length of the phone number for this country.

If a number is really malformed, it throws a `PhoneNumberParseException`:

```php
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;

try {
    $number = PhoneNumber::parse('+333');
}
catch (PhoneNumberParseException $e) {
    // 'The string supplied is too short to be a phone number.'
}
```

In most cases, it is recommended to perform an extra step of validation with `isValidNumber()` or `isPossibleNumber()`:

```php
if (! $number->isPossibleNumber()) {
    // a more lenient and faster check than `isValidNumber()`
}

if (! $number->isValidNumber()) {
    // strict check relying on up-to-date metadata library
}

```

As a rule of thumb, do the following:

- When the number comes from user input, do a full validation: `parse()` and catch `PhoneNumberParseException`, then call `isValidNumber()` (or `isPossibleNumber()` for a more lenient check) if no exception occurred;
- When the number is later retrieved from your database, and has been validated before, you can just perform a blind `parse()`.

### Formatting a number

#### Basic formatting

You can use `format()` with constants from the [PhoneNumberFormat](https://github.com/brick/phonenumber/blob/master/src/PhoneNumberFormat.php) class:

```php
$number = PhoneNumber::parse('+41446681800');
$number->format(PhoneNumberFormat::E164); // +41446681800
$number->format(PhoneNumberFormat::INTERNATIONAL); // +41 44 668 18 00
$number->format(PhoneNumberFormat::NATIONAL); // 044 668 18 00
$number->format(PhoneNumberFormat::RFC3966); // tel:+41-44-668-18-00
```

#### Formatting to call from another country

You may want to present a phone number to an audience in a specific country, with the correct international 
prefix when required. This is what `formatForCallingFrom()` does:

```php
$number = PhoneNumber::parse('+447123456789');
$number->formatForCallingFrom('GB'); // 07123 456789
$number->formatForCallingFrom('FR'); // 00 44 7123 456789
$number->formatForCallingFrom('US'); // 011 44 7123 456789
```

### Number types

In certain cases, it is possible to know the type of a phone number (fixed line, mobile phone, etc.), using
the `getNumberType()` method, which returns a constant from the [PhoneNumberType](https://github.com/brick/phonenumber/blob/master/src/PhoneNumberType.php) class:

```php
PhoneNumber::parse('+336123456789')->getNumberType(); // PhoneNumberType::MOBILE
PhoneNumber::parse('+33123456789')->getNumberType(); // PhoneNumberType::FIXED_LINE
```

If the type is unknown, the `PhoneNumberType::UNKNOWN` value is returned.
Check the `PhoneNumberType` class for all possible values.

### Number information

You can extract the following information from a phone number:

```php
$number = PhoneNumber::parse('+447123456789');
echo $number->getRegionCode(); // GB
echo $number->getCountryCode(); // 44
echo $number->getNationalNumber(); // 7123456789
```

### Example numbers

You can get an example number for a country code and an optional number type (defaults to fixed line).
This can be useful to use as a placeholder in an input field, for example:

```php
echo PhoneNumber::getExampleNumber('FR'); // +33123456789
echo PhoneNumber::getExampleNumber('FR', PhoneNumberType::MOBILE); // +33612345678
```

The return type of `getExampleNumber()` is a `PhoneNumber` instance, so you can format it as you like:

```php
echo PhoneNumber::getExampleNumber('FR')->formatForCallingFrom('FR'); // 01 23 45 67 89
```

If no example phone number is available for the country code / number type combination, a `PhoneNumberException` is thrown.

### Casting to string

Casting a `PhoneNumber` to string returns its E164 representation (`+` followed by digits), so the following are equivalent:

```php
(string) $phoneNumber
```

```php
$phoneNumber->format(PhoneNumberFormat::E164)
```

You can serialize a `PhoneNumber` to string, then recover it using `parse()` without a country code:

```php
$phoneNumber = PhoneNumber::parse('02079834000', 'GB');
$phoneNumberAsString = (string) $phoneNumber; // +442079834000
$phoneNumber2 = PhoneNumber::parse($phoneNumberAsString);

$phoneNumber2->isEqualTo($phoneNumber); // true
```

### Doctrine mappings

You can use `PhoneNumber` objects in your Doctrine entities using the [brick/phonenumber-doctrine](https://github.com/brick/phonenumber-doctrine) package.
