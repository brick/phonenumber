Brick\PhoneNumber
=================

A phone number library for PHP.

[![Build Status](https://secure.travis-ci.org/brick/phonenumber.svg?branch=master)](http://travis-ci.org/brick/phonenumber)
[![Coverage Status](https://coveralls.io/repos/brick/phonenumber/badge.svg?branch=master)](https://coveralls.io/r/brick/phonenumber?branch=master)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

This library is a thin wrapper around [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php),
itself a port of [Google's libphonenumber](https://github.com/googlei18n/libphonenumber).

It provides an equivalent functionality, with the following implementation differences:

- `PhoneNumber` is an immutable class; it can be safely passed around without having to worry about the risk for it to be changed;
- `PhoneNumber` is not just a mere data container, but provides all the methods to parse, format and validate phone numbers; it transparently encapsulates `PhoneNumberUtil`.

# Installation

This library is installable via [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "brick/phonenumber": "dev-master"
        }
    }

# Quick start

All the classes lie in the `Brick\PhoneNumber` namespace.

To obtain an instance of `PhoneNumber`, use the `parse()` method:

- Using an international number: `PhoneNumber::parse('+336123456789')`;
- Using a national number and a country code: `PhoneNumber::parse('01 23 45 67 89', 'FR')`;

## Validating a number

The `parse()` method is quite permissive with numbers; it basically attempts to match a country code,
and validates the length of the phone number for this country.

If a number is really malformed, it throws a `PhoneNumberParseException`:

    try {
        $number = PhoneNumber::parse('+333');
    }
    catch (PhoneNumberParseException $e) {
        // 'The string supplied is too short to be a phone number.'
    }

In most cases, it is recommended to perform an extra step of validation with `isValidNumber()`:

    PhoneNumber::parse('+33123456789')->isValidNumber(); // true
    PhoneNumber::parse('+331234567890')->isValidNumber(); // false

As a rule of thumb, do the following:

- When the number comes from a user input, call `isValidNumber()` (don't forget to check for `PhoneNumberParseException`, too)
- When the number is later retrieved from your database, but has been validated before, do not use `isValidNumber()`

## Formatting a number

### Basic formatting

You can use `format()` with constants from the `PhoneNumberFormat` class:

    $number = PhoneNumber::parse('+41446681800');
    $number->format(PhoneNumberFormat::E164); // +41446681800
    $number->format(PhoneNumberFormat::INTERNATIONAL); // +41 44 668 18 00
    $number->format(PhoneNumberFormat::NATIONAL); // 044 668 18 00
    $number->format(PhoneNumberFormat::RFC3966); // tel:+41-44-668-18-00

### Formatting to call from another country

You may want to present a phone number to an audience in a specific country, with the correct international 
prefix when required. This is what `formatForCallingFrom()` does:

    $number = PhoneNumber::parse('+447123456789');
    $number->formatForCallingFrom('GB'); // 07123 456789
    $number->formatForCallingFrom('FR'); // 00 44 7123 456789
    $number->formatForCallingFrom('US'); // 011 44 7123 456789

## Number types

In certain cases, it is possible to know the type of a phone number (fixed line, mobile phone, etc.), using
the `getNumberType()` method, which returns a constant from the `PhoneNumberType` class:

    PhoneNumber::parse('+336123456789')->getNumberType(); // PhoneNumberType::MOBILE
    PhoneNumber::parse('+33123456789')->getNumberType(); // PhoneNumberType::FIXED_LINE

If the type is unknown, the `PhoneNumberType::UNKNOWN` value is returned.
Check the `PhoneNumberType` class for all possible values.
