<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

/**
 * Enum values for carrier name mode.
 */
enum CarrierNameMode
{
    /**
     * Always return the carrier name when it is available.
     */
    case ALWAYS;

    /**
     * Return the carrier name only when the number is a mobile number.
     */
    case MOBILE_ONLY;

    /**
     * Return the carrier name only when the number is a mobile number,
     * and the region does not support mobile number portability.
     */
    case MOBILE_NO_PORTABILITY_ONLY;
}
