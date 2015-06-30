<?php

namespace Brick\PhoneNumber;

/**
 * Exception thrown when a number cannot be parsed.
 */
class PhoneNumberParseException extends \Exception
{
    /**
     * @param \Exception $e
     *
     * @return PhoneNumberParseException
     */
    public static function wrap(\Exception $e)
    {
        return new PhoneNumberParseException($e->getMessage(), $e->getCode(), $e);
    }
}
