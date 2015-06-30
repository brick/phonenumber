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
- `PhoneNumber` is not just a mere data container, but provides all the methods to parse and validate data; it transparently encapsulates `PhoneNumberUtil`.

Installation
------------

This library is installable via [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "brick/phonenumber": "dev-master"
        }
    }

