<?php

namespace Brick\PhoneNumber;

/**
 * Exception thrown when a phone number cannot be parsed.
 */
class PhoneNumberParseException extends PhoneNumberException
{
    /**
     * @internal
     *
     * @param \Exception $e
     *
     * @return PhoneNumberParseException
     */
    public static function wrap(\Exception $e)
    {
        return new PhoneNumberParseException($e->getMessage(), $e->getCode(), $e);
    }
}
