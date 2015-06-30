<?php

namespace Brick\PhoneNumber;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * A phone number.
 */
class PhoneNumber
{
    /**
     * The underlying PhoneNumber object from libphonenumber.
     *
     * @var \libphonenumber\PhoneNumber
     */
    private $phoneNumber;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param \libphonenumber\PhoneNumber $phoneNumber
     */
    private function __construct(\libphonenumber\PhoneNumber $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Parses a string representation of a phone number.
     *
     * @param string      $phoneNumber The phone number to parse.
     * @param string|null $regionCode  The region code to assume, if the number is not in international format.
     *
     * @return PhoneNumber
     *
     * @throws PhoneNumberParseException
     */
    public static function parse($phoneNumber, $regionCode = null)
    {
        try {
            return new PhoneNumber(
                PhoneNumberUtil::getInstance()->parse($phoneNumber, $regionCode)
            );
        } catch (NumberParseException $e) {
            throw PhoneNumberParseException::wrap($e);
        }
    }

    /**
     * Returns the country code of this PhoneNumber.
     *
     * The country code is a series of 1 to 3 digits, as defined per the E.164 recommendation.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return (string) $this->phoneNumber->getCountryCode();
    }

    /**
     * Returns the national number of this PhoneNumber.
     *
     * The national number is a series of digits.
     *
     * @return string
     */
    public function getNationalNumber()
    {
        return $this->phoneNumber->getNationalNumber();
    }

    /**
     * Returns the region code of this PhoneNumber.
     *
     * The region code is an ISO 3166-1 alpha-2 country code.
     *
     * If the phone number does not map to a geographic region
     * (global networks, such as satellite phone numbers) this method returns null.
     *
     * @return string|null The region code, or null if the number does not map to a geographic region.
     */
    public function getRegionCode()
    {
        $regionCode = PhoneNumberUtil::getInstance()->getRegionCodeForNumber($this->phoneNumber);

        if ($regionCode === '001') {
            return null;
        }

        return $regionCode;
    }

    /**
     * @return boolean
     */
    public function isValidNumber()
    {
        return PhoneNumberUtil::getInstance()->isValidNumber($this->phoneNumber);
    }

    /**
     * Returns the type of this phone number.
     *
     * @return int One of the PhoneNumberType constants.
     */
    public function getNumberType()
    {
        return PhoneNumberUtil::getInstance()->getNumberType($this->phoneNumber);
    }

    /**
     * @param int $format One of the PhoneNumberFormat constants.
     *
     * @return string
     */
    public function format($format)
    {
        return PhoneNumberUtil::getInstance()->format($this->phoneNumber, $format);
    }

    /**
     * Returns a string representation of this phone number in international format.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->phoneNumber;
    }
}
