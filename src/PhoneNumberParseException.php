<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

use libphonenumber\NumberParseException;

/**
 * Exception thrown when a phone number cannot be parsed.
 */
final class PhoneNumberParseException extends PhoneNumberException
{
    public readonly PhoneNumberParseErrorType $errorType;

    /**
     * @internal
     */
    public function __construct(NumberParseException $exception)
    {
        /** @var int $errorType */
        $errorType = $exception->getErrorType();

        parent::__construct($exception->getMessage(), $errorType, $exception);

        $this->errorType = PhoneNumberParseErrorType::from($errorType);
    }
}
