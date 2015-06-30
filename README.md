Brick\PhoneNumber
=================

A phone number library for PHP.

This library is a thin wrapper around [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php),
itself a port of [Google's libphonenumber](https://github.com/googlei18n/libphonenumber).

It provides an equivalent functionality, with the following implementation differences:

- `PhoneNumber` is an immutable class; it can be safely passed around without having to worry about the risk for it to be changed;
- `PhoneNumber` is not just a mere data container, but provides all the methods to parse and validate data; it transparently encapsulates `PhoneNumberUtil`.

