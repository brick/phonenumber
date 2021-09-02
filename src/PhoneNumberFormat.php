<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

/**
 * Constants for the phone number formats.
 */
final class PhoneNumberFormat
{
    /**
     * The E164 format.
     *
     * This consists of a + sign followed by a series of digits,
     * comprising the country code and national number.
     *
     * Example: `+41446681800`.
     */
    public const E164 = 0;

    /**
     * The international format.
     *
     * This is similar to the E164 format, with extra formatting.
     * This format is consistent with the definition in ITU-T Recommendation E123.
     *
     * Example: `+41 44 668 1800`.
     */
    public const INTERNATIONAL = 1;

    /**
     * The national format.
     *
     * This is the number as it would be composed from within the country, with formatting.
     * This format is consistent with the definition in ITU-T Recommendation E123.
     *
     * Example: `044 668 1800`.
     */
    public const NATIONAL = 2;

    /**
     * The RFC 3966 format.
     *
     * This format outputs a `tel:` URI that can be used as an anchor link to start a VOIP call from a web page.
     *
     * Example: `tel:+41-44-668-1800`.
     */
    public const RFC3966 = 3;
}
