<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

use libphonenumber\NumberParseException;

/**
 * Exception thrown when a phone number cannot be parsed.
 */
final class PhoneNumberParseException extends PhoneNumberException
{
    /**
     * @internal
     */
    public static function wrap(NumberParseException $e) : PhoneNumberParseException
    {
        return new PhoneNumberParseException($e->getMessage(), $e->getCode(), $e);
    }
}
