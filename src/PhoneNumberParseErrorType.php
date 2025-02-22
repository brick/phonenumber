<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

/**
 * Enum values for the parse error types.
 */
enum PhoneNumberParseErrorType: int
{
    /**
     * The country code supplied did not belong to a supported country or non-geographical entity.
     */
    case INVALID_COUNTRY_CODE = 0;

    /**
     * This indicates the string passed is not a valid number. Either the string had less than 3
     * digits in it or had an invalid phone-context parameter.
     */
    case NOT_A_NUMBER = 1;

    /**
     * This indicates the string started with an international dialing prefix, but after this was
     * stripped from the number, had fewer digits than any valid phone number (including country
     * code) could have.
     */
    case TOO_SHORT_AFTER_IDD = 2;

    /**
     * This indicates the string, after any country code has been stripped, had fewer digits than any
     * valid phone number could have.
     */
    case TOO_SHORT_NSN = 3;

    /**
     * This indicates the string had more digits than any valid phone number could have.
     */
    case TOO_LONG = 4;
}
