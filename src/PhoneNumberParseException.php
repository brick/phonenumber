<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

/**
 * Exception thrown when a phone number cannot be parsed.
 */
final class PhoneNumberParseException extends PhoneNumberException
{
    /**
     * @internal
     *
     * @param \Exception $e
     *
     * @return PhoneNumberParseException
     */
    public static function wrap(\Exception $e) : PhoneNumberParseException
    {
        return new PhoneNumberParseException($e->getMessage(), $e->getCode(), $e);
    }
}
