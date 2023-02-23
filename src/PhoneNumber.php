<?php

declare(strict_types=1);

namespace Brick\PhoneNumber;

use JsonSerializable;
use libphonenumber;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * A phone number.
 */
final class PhoneNumber implements JsonSerializable
{
    /**
     * The underlying PhoneNumber object from libphonenumber.
     */
    private libphonenumber\PhoneNumber $phoneNumber;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     */
    private function __construct(libphonenumber\PhoneNumber $phoneNumber)
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
    public static function parse(string $phoneNumber, ?string $regionCode = null) : PhoneNumber
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
     * @param string             $regionCode      The region code.
     * @param PhoneNumberType::* $phoneNumberType The phone number type, defaults to a fixed line.
     *
     * @return PhoneNumber
     *
     * @throws PhoneNumberException If no example number is available for this region and type.
     */
    public static function getExampleNumber(string $regionCode, int $phoneNumberType = PhoneNumberType::FIXED_LINE) : PhoneNumber
    {
        $phoneNumber = PhoneNumberUtil::getInstance()->getExampleNumberForType($regionCode, $phoneNumberType);

        if ($phoneNumber === null) {
            throw new PhoneNumberException('No example number is available for the given region and type.');
        }

        return new PhoneNumber($phoneNumber);
    }

    /**
     * Returns the country code of this PhoneNumber.
     *
     * The country code is a series of 1 to 3 digits, as defined per the E.164 recommendation.
     *
     * @return string
     */
    public function getCountryCode() : string
    {
        return (string) $this->phoneNumber->getCountryCode();
    }

    /**
     * Returns the geographical area code of this PhoneNumber.
     *
     * Notes:
     *
     *  - geographical area codes change over time, and this method honors those changes; therefore, it doesn't
     *    guarantee the stability of the result it produces;
     *  - most non-geographical numbers have no area codes, including numbers from non-geographical entities;
     *  - some geographical numbers have no area codes.
     *
     * If this number has no area code, an empty string is returned.
     *
     * @return string
     */
    public function getGeographicalAreaCode() : string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $nationalSignificantNumber = $phoneNumberUtil->getNationalSignificantNumber($this->phoneNumber);

        $areaCodeLength = $phoneNumberUtil->getLengthOfGeographicalAreaCode($this->phoneNumber);

        return substr($nationalSignificantNumber, 0, $areaCodeLength);
    }

    /**
     * Returns the national number of this PhoneNumber.
     *
     * The national number is a series of digits.
     *
     * @return string
     */
    public function getNationalNumber() : string
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
    public function getRegionCode() : ?string
    {
        $regionCode = PhoneNumberUtil::getInstance()->getRegionCodeForNumber($this->phoneNumber);

        if ($regionCode === '001') {
            return null;
        }

        return $regionCode;
    }

    /**
     * Returns whether this phone number is a possible number.
     *
     * Note this provides a more lenient and faster check than `isValidNumber()`.
     *
     * @return bool
     */
    public function isPossibleNumber() : bool
    {
        return PhoneNumberUtil::getInstance()->isPossibleNumber($this->phoneNumber);
    }

    /**
     * Returns whether this phone number matches a valid pattern.
     *
     * Note this doesn't verify the number is actually in use,
     * which is impossible to tell by just looking at a number itself.
     *
     * @return bool
     */
    public function isValidNumber() : bool
    {
        return PhoneNumberUtil::getInstance()->isValidNumber($this->phoneNumber);
    }

    /**
     * Returns the type of this phone number.
     *
     * @return PhoneNumberType::*
     */
    public function getNumberType() : int
    {
        return PhoneNumberUtil::getInstance()->getNumberType($this->phoneNumber);
    }

    /**
     * Returns a formatted string representation of this phone number.
     *
     * @param PhoneNumberFormat::* $format
     *
     * @return string
     */
    public function format(int $format) : string
    {
        return PhoneNumberUtil::getInstance()->format($this->phoneNumber, $format);
    }

    /**
     * Formats this phone number for out-of-country dialing purposes.
     *
     * @param string $regionCode The ISO 3166-1 alpha-2 country code
     *
     * @return string
     */
    public function formatForCallingFrom(string $regionCode) : string
    {
        return PhoneNumberUtil::getInstance()->formatOutOfCountryCallingNumber($this->phoneNumber, $regionCode);
    }

    public function isEqualTo(PhoneNumber $phoneNumber): bool
    {
        return $this->phoneNumber->equals($phoneNumber->phoneNumber);
    }

    /**
     * Required by interface JsonSerializable.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    /**
     * Returns a text description for the given phone number, in the language provided. The description might consist of
     * the name of the country where the phone number is from, or the name of the geographical area the phone number is
     * from if more detailed information is available.
     *
     * If $userRegion is set, we also consider the region of the user. If the phone number is from the same region as
     * the user, only a lower-level description will be returned, if one exists. Otherwise, the phone number's region
     * will be returned, with optionally some more detailed information.
     *
     * For example, for a user from the region "US" (United States), we would show "Mountain View, CA" for a particular
     * number, omitting the United States from the description. For a user from the United Kingdom (region "GB"), for
     * the same number we may show "Mountain View, CA, United States" or even just "United States".
     *
     * If no description is found, this method returns null.
     *
     * @param string      $locale     The locale for which the description should be written.
     * @param string|null $userRegion The region code for a given user. This region will be omitted from the description
     *                                if the phone number comes from this region. It is a two-letter uppercase CLDR
     *                                region code.
     *
     * @return string|null
     */
    public function getDescription(string $locale, ?string $userRegion = null) : ?string
    {
        $description = PhoneNumberOfflineGeocoder::getInstance()->getDescriptionForNumber(
            $this->phoneNumber,
            $locale,
            $userRegion
        );

        if ($description === '') {
            return null;
        }

        return $description;
    }

    /**
     * Returns a string representation of this phone number in international E164 format.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->format(PhoneNumberFormat::E164);
    }
}
