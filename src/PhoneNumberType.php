<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

/**
 * Constants for the phone number types.
 */
final class PhoneNumberType
{
    /**
     * Fixed line number.
     */
    public const FIXED_LINE = 0;

    /**
     * Mobile number.
     */
    public const MOBILE = 1;

    /**
     * Fixed line or mobile number.
     *
     * In some regions (e.g. the USA), it is impossible to distinguish between fixed-line and
     * mobile numbers by looking at the phone number itself.
     */
    public const FIXED_LINE_OR_MOBILE = 2;

    /**
     * Freephone number.
     */
    public const TOLL_FREE = 3;

    /**
     * Premium rate number.
     */
    public const PREMIUM_RATE = 4;

    /**
     * Shared cost number.
     *
     * The cost of this call is shared between the caller and the recipient, and is hence typically
     * less than PREMIUM_RATE calls.
     *
     * @see http://en.wikipedia.org/wiki/Shared_Cost_Service
     */
    public const SHARED_COST = 5;

    /**
     * Voice over IP number.
     *
     * This includes TSoIP (Telephony Service over IP).
     */
    public const VOIP = 6;

    /**
     * Personal number.
     *
     * A personal number is associated with a particular person, and may be routed to either a
     * MOBILE or FIXED_LINE number.
     *
     * @see http://en.wikipedia.org/wiki/Personal_Numbers
     */
    public const PERSONAL_NUMBER = 7;

    /**
     * Pager number.
     */
    public const PAGER = 8;

    /**
     * Universal Access Number or Company Number.
     *
     * The number may be further routed to specific offices, but allows one number to be used for a company.
     */
    public const UAN = 9;

    /**
     * Unknown number type.
     *
     * A phone number is of type UNKNOWN when it does not fit any of the known patterns
     * for a specific region.
     */
    public const UNKNOWN = 10;

    /**
     * Emergency number.
     */
    public const EMERGENCY = 27;

    /**
     * Voicemail number.
     */
    public const VOICEMAIL = 28;

    /**
     * Short code number.
     */
    public const SHORT_CODE = 29;

    /**
     * Standard rate number.
     */
    public const STANDARD_RATE = 30;
}
