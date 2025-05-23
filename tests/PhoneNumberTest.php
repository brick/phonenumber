<?php

declare(strict_types=1);

namespace Brick\PhoneNumber\Tests;

use Brick\PhoneNumber\CarrierNameMode;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseErrorType;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberType;
use Composer\InstalledVersions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for class PhoneNumber.
 */
class PhoneNumberTest extends TestCase
{
    private const AR_MOBILE = '+5491187654321';
    private const AR_NUMBER = '+541187654321';
    private const AU_NUMBER = '+61236618300';
    private const BS_MOBILE = '+12423570000';
    private const BS_NUMBER = '+12423651234';
    // Note that this is the same as the example number for DE in the metadata.
    private const DE_NUMBER = '+4930123456';
    private const GB_MOBILE = '+447912345678';
    private const GB_NUMBER = '+442070313000';
    private const IT_MOBILE = '+39345678901';
    private const IT_NUMBER = '+390236618300';
    // Numbers to test the formatting rules from Mexico.
    private const MX_MOBILE1 = '+5212345678900';
    private const MX_MOBILE2 = '+5215512345678';
    private const MX_NUMBER1 = '+523312345678';
    private const MX_NUMBER2 = '+528211234567';
    private const NZ_NUMBER = '+6433316005';
    private const US_NUMBER = '+16502530000';
    private const US_PREMIUM = '+19002530000';
    // Too short, but still possible US numbers.
    private const US_LOCAL_NUMBER = '+12530000';
    private const US_TOLLFREE = '+18002530000';
    private const INTERNATIONAL_TOLL_FREE = '+80012345678';
    // We set this to be the same length as numbers for the other non-geographical country prefix that
    // we have in our test metadata. However, this is not considered valid because they differ in
    // their country calling code.
    private const INTERNATIONAL_TOLL_FREE_TOO_LONG = '+800123456789';
    private const UNIVERSAL_PREMIUM_RATE = '+979123456789';

    #[DataProvider('providerGetExampleNumber')]
    public function testGetExampleNumber(string $regionCode, string $callingCode, ?PhoneNumberType $numberType = null) : void
    {
        if ($numberType === null) {
            $phoneNumber = PhoneNumber::getExampleNumber($regionCode);
        } else {
            $phoneNumber = PhoneNumber::getExampleNumber($regionCode, $numberType);
        }

        self::assertInstanceOf(PhoneNumber::class, $phoneNumber);
        self::assertTrue($phoneNumber->isValidNumber());

        if ($numberType !== null) {
            self::assertSame($numberType, $phoneNumber->getNumberType());
        }

        self::assertSame($callingCode, $phoneNumber->getCountryCode());
        self::assertSame($regionCode, $phoneNumber->getRegionCode());
    }

    public static function providerGetExampleNumber() : array
    {
        return [
            ['US', '1'],
            ['FR', '33', PhoneNumberType::FIXED_LINE],
            ['FR', '33', PhoneNumberType::MOBILE],
            ['GB', '44', PhoneNumberType::FIXED_LINE],
            ['GB', '44', PhoneNumberType::MOBILE],
        ];
    }

    public function testGetExampleNumberThrowsExceptionForInvalidRegionCode() : void
    {
        $this->expectException(PhoneNumberException::class);
        PhoneNumber::getExampleNumber('ZZ');
    }

    #[DataProvider('providerGetNationalNumber')]
    public function testGetNationalNumber(string $expectedNationalNumber, string $phoneNumber) : void
    {
        self::assertSame($expectedNationalNumber, PhoneNumber::parse($phoneNumber)->getNationalNumber());
    }

    public static function providerGetNationalNumber() : array
    {
        return [
            ['6502530000', self::US_NUMBER],
            ['345678901', self::IT_MOBILE],
            ['236618300', self::IT_NUMBER],
            ['12345678', self::INTERNATIONAL_TOLL_FREE]
        ];
    }

    #[DataProvider('providerParseNationalNumber')]
    public function testParseNationalNumber(
        string $expectedNumber,
        string $numberToParse,
        string $regionCode,
        ?string $minimumUpstreamVersion = null,
    ) : void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        self::assertSame($expectedNumber, (string) PhoneNumber::parse($numberToParse, $regionCode));
    }

    public static function providerParseNationalNumber() : array
    {
        return [
            // National prefix attached.
            [self::NZ_NUMBER, '033316005', 'NZ'],
            [self::NZ_NUMBER, '33316005', 'NZ'],

            // National prefix attached and some formatting present.
            [self::NZ_NUMBER, '03-331 6005', 'NZ'],
            [self::NZ_NUMBER, '03 331 6005', 'NZ'],

            // Testing international prefixes.
            // Should strip country calling code.
            [self::NZ_NUMBER, '0064 3 331 6005', 'NZ'],

            // Try again, but this time we have an international number with Region Code US.
            // It should recognise the country calling code and parse accordingly.
            [self::NZ_NUMBER, '01164 3 331 6005', 'US'],
            [self::NZ_NUMBER, '+64 3 331 6005', 'US'],

            ['+6464123456', '64(0)64123456', 'NZ', '8.5.2'],

            // Check that using a '/' is fine in a phone number.
            [self::DE_NUMBER, '301/23456', 'DE'],

            // Check it doesn't use the '1' as a country calling code
            // when parsing if the phone number was already possible
            ['+11234567890', '123-456-7890', 'US', '8.0.0']
        ];
    }

    #[DataProvider('providerGetRegionCode')]
    public function testGetRegionCode(?string $expectedRegion, string $phoneNumber) : void
    {
        self::assertSame($expectedRegion, PhoneNumber::parse($phoneNumber)->getRegionCode());
    }

    public static function providerGetRegionCode() : array
    {
        return [
            ['BS', self::BS_NUMBER],
            ['US', self::US_NUMBER],
            ['GB', self::GB_MOBILE],
            [null, self::INTERNATIONAL_TOLL_FREE],
        ];
    }

    #[DataProvider('providerGetNumberType')]
    public function testGetNumberType(
        PhoneNumberType $numberType,
        string $phoneNumber,
        ?string $minimumUpstreamVersion = null,
    ) : void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        self::assertSame($numberType, PhoneNumber::parse($phoneNumber)->getNumberType());
    }

    public static function providerGetNumberType() : array
    {
        return [
            [PhoneNumberType::PREMIUM_RATE, self::US_PREMIUM],
            [PhoneNumberType::PREMIUM_RATE, '+39892123'],
            [PhoneNumberType::PREMIUM_RATE, '+449187654321'],
            [PhoneNumberType::PREMIUM_RATE, '+499001654321'],
            [PhoneNumberType::PREMIUM_RATE, '+4990091234567'],
            [PhoneNumberType::PREMIUM_RATE, self::UNIVERSAL_PREMIUM_RATE],
            [PhoneNumberType::TOLL_FREE, '+39803123'],
            [PhoneNumberType::TOLL_FREE, '+498001234567'],
            [PhoneNumberType::TOLL_FREE, self::INTERNATIONAL_TOLL_FREE],

            [PhoneNumberType::MOBILE, self::BS_MOBILE],
            [PhoneNumberType::MOBILE, self::GB_MOBILE],
            [PhoneNumberType::MOBILE, self::IT_MOBILE, '8.9.11'],
            [PhoneNumberType::MOBILE, self::AR_MOBILE],
            [PhoneNumberType::MOBILE, '+4915123456789'],

            [PhoneNumberType::FIXED_LINE, self::BS_NUMBER],
            [PhoneNumberType::FIXED_LINE, self::IT_NUMBER],
            [PhoneNumberType::FIXED_LINE, self::GB_NUMBER],
            [PhoneNumberType::FIXED_LINE, self::DE_NUMBER],

            [PhoneNumberType::FIXED_LINE_OR_MOBILE, self::US_NUMBER],

            [PhoneNumberType::VOIP, '+445631231234'],

            [PhoneNumberType::PERSONAL_NUMBER, '+447031231234'],

            [PhoneNumberType::UNKNOWN, self::US_LOCAL_NUMBER]
        ];
    }

    #[DataProvider('providerValidNumbers')]
    #[DataProvider('providerPossibleButNotValidNumbers')]
    public function testIsPossibleNumber(string $phoneNumber) : void
    {
        self::assertTrue(PhoneNumber::parse($phoneNumber)->isPossibleNumber());
    }

    #[DataProvider('providerNotPossibleNumbers')]
    public function testIsNotPossibleNumber(string $phoneNumber) : void
    {
        self::assertFalse(PhoneNumber::parse($phoneNumber)->isPossibleNumber());
    }

    #[DataProvider('providerValidNumbers')]
    public function testIsValidNumber(string $phoneNumber) : void
    {
        self::assertTrue(PhoneNumber::parse($phoneNumber)->isValidNumber());
    }

    #[DataProvider('providerNotPossibleNumbers')]
    #[DataProvider('providerPossibleButNotValidNumbers')]
    public function testIsNotValidNumber(string $phoneNumber) : void
    {
        self::assertFalse(PhoneNumber::parse($phoneNumber)->isValidNumber());
    }

    public static function providerValidNumbers() : array
    {
        return [
            [self::US_NUMBER],
            [self::IT_NUMBER],
            [self::GB_MOBILE],
            [self::INTERNATIONAL_TOLL_FREE],
            [self::UNIVERSAL_PREMIUM_RATE],
            ['+6421387835']
        ];
    }

    public static function providerPossibleButNotValidNumbers() : array
    {
        return [
            [self::US_LOCAL_NUMBER],
            ['+3923661830000'],
            ['+44791234567'],
            ['+491234'],
            ['+643316005'],
            ['+39232366']
        ];
    }

    public static function providerNotPossibleNumbers() : array
    {
        return [
            [self::INTERNATIONAL_TOLL_FREE_TOO_LONG],
            ['+1253000']
        ];
    }

    #[DataProvider('providerParseException')]
    public function testParseException(
        string $phoneNumber,
        ?string $regionCode,
        PhoneNumberParseErrorType $errorType,
    ) : void {
        try {
            PhoneNumber::parse($phoneNumber, $regionCode);
        } catch (PhoneNumberParseException $e) {
            self::assertSame($errorType, $e->errorType);
            self::assertSame($errorType->value, $e->getCode());

            return;
        }

        self::fail('Expected PhoneNumberParseException was not thrown.');
    }

    public static function providerParseException() : array
    {
        return [
            // Empty string.
            ['', null, PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['', 'US', PhoneNumberParseErrorType::NOT_A_NUMBER],

            ['This is not a phone number', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['1 Still not a number', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['1 MICROSOFT', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['12 MICROSOFT', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['01495 72553301873 810104', 'GB', PhoneNumberParseErrorType::TOO_LONG],
            ['+---', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['+***', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['+*******91', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
            ['+ 00 210 3 331 6005', 'NZ', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],

            // Too short.
            ['+49 0', 'DE', PhoneNumberParseErrorType::TOO_SHORT_NSN],

            // Does not match a country code.
            ['+02366', null, PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
            ['+210 3456 56789', 'NZ', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],

            // A region code must be given if not in international format.
            ['123 456 7890', null, PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],

            // Unknown region code (deprecated and removed from ISO 3166-2).
            ['123 456 7890', 'CS', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],

            // No number, only region code.
            ['0044', 'GB', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],
            ['0044------', 'GB', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],

            // Only IDD provided.
            ['011', 'US', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],

            // Only IDD and then 9.
            ['0119', 'US', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD]
        ];
    }

    #[DataProvider('providerFormat')]
    public function testFormat(
        string $expected,
        string $phoneNumber,
        PhoneNumberFormat $numberFormat,
        ?string $minimumUpstreamVersion = null,
    ) : void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        self::assertSame($expected, PhoneNumber::parse($phoneNumber)->format($numberFormat));
    }

    public static function providerFormat() : array
    {
        return [
            // US
            ['(650) 253-0000', self::US_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+1 650-253-0000', self::US_NUMBER, PhoneNumberFormat::INTERNATIONAL],

            ['(800) 253-0000', self::US_TOLLFREE, PhoneNumberFormat::NATIONAL],
            ['+1 800-253-0000', self::US_TOLLFREE, PhoneNumberFormat::INTERNATIONAL],

            ['(900) 253-0000', self::US_PREMIUM, PhoneNumberFormat::NATIONAL],
            ['+1 900-253-0000', self::US_PREMIUM, PhoneNumberFormat::INTERNATIONAL],

            ['tel:+1-900-253-0000', self::US_PREMIUM, PhoneNumberFormat::RFC3966],

            // BS
            ['(242) 365-1234', self::BS_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+1 242-365-1234', self::BS_NUMBER, PhoneNumberFormat::INTERNATIONAL],

            // GB
            ['020 7031 3000', self::GB_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+44 20 7031 3000', self::GB_NUMBER, PhoneNumberFormat::INTERNATIONAL],

            ['07912 345678', self::GB_MOBILE, PhoneNumberFormat::NATIONAL],
            ['+44 7912 345678', self::GB_MOBILE, PhoneNumberFormat::INTERNATIONAL],

            // DE
            ['030 1234', '+49301234', PhoneNumberFormat::NATIONAL],
            ['+49 30 1234', '+49301234', PhoneNumberFormat::INTERNATIONAL],
            ['tel:+49-30-1234', '+49301234', PhoneNumberFormat::RFC3966],

            ['0291 123', '+49291123', PhoneNumberFormat::NATIONAL],
            ['+49 291 123', '+49291123', PhoneNumberFormat::INTERNATIONAL],

            ['0291 12345678', '+4929112345678', PhoneNumberFormat::NATIONAL],
            ['+49 291 12345678', '+4929112345678', PhoneNumberFormat::INTERNATIONAL],

            ['09123 12345', '+49912312345', PhoneNumberFormat::NATIONAL],
            ['+49 9123 12345', '+49912312345', PhoneNumberFormat::INTERNATIONAL],

            ['08021 2345', '+4980212345', PhoneNumberFormat::NATIONAL],
            ['+49 8021 2345', '+4980212345', PhoneNumberFormat::INTERNATIONAL],

            ['030 123456', self::DE_NUMBER, PhoneNumberFormat::NATIONAL],

            ['04134 1234', '+4941341234', PhoneNumberFormat::NATIONAL],

            // IT
            ['02 3661 8300', self::IT_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+39 02 3661 8300', self::IT_NUMBER, PhoneNumberFormat::INTERNATIONAL],
            ['+390236618300', self::IT_NUMBER, PhoneNumberFormat::E164],

            ['345 678 901', self::IT_MOBILE, PhoneNumberFormat::NATIONAL],
            ['+39 345 678 901', self::IT_MOBILE, PhoneNumberFormat::INTERNATIONAL],
            ['+39345678901', self::IT_MOBILE, PhoneNumberFormat::E164],

            // AU
            ['(02) 3661 8300', self::AU_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+61 2 3661 8300', self::AU_NUMBER, PhoneNumberFormat::INTERNATIONAL],
            ['+61236618300', self::AU_NUMBER, PhoneNumberFormat::E164],

            ['1800 123 456', '+611800123456', PhoneNumberFormat::NATIONAL],
            ['+61 1800 123 456', '+611800123456', PhoneNumberFormat::INTERNATIONAL],
            ['+611800123456', '+611800123456', PhoneNumberFormat::E164],

            // AR
            ['011 8765-4321', self::AR_NUMBER, PhoneNumberFormat::NATIONAL],
            ['+54 11 8765-4321', self::AR_NUMBER, PhoneNumberFormat::INTERNATIONAL],
            ['+541187654321', self::AR_NUMBER, PhoneNumberFormat::E164],

            ['011 15-8765-4321', self::AR_MOBILE, PhoneNumberFormat::NATIONAL],
            ['+54 9 11 8765-4321', self::AR_MOBILE, PhoneNumberFormat::INTERNATIONAL],
            ['+5491187654321', self::AR_MOBILE, PhoneNumberFormat::E164],

            // MX
            ['12345678900', self::MX_MOBILE1, PhoneNumberFormat::NATIONAL, '8.13.38'],
            ['+52 12345678900', self::MX_MOBILE1, PhoneNumberFormat::INTERNATIONAL, '8.13.38'],
            ['+5212345678900', self::MX_MOBILE1, PhoneNumberFormat::E164, '8.13.38'],

            ['15512345678', self::MX_MOBILE2, PhoneNumberFormat::NATIONAL, '8.13.38'],
            ['+52 15512345678', self::MX_MOBILE2, PhoneNumberFormat::INTERNATIONAL, '8.13.38'],
            ['+5215512345678', self::MX_MOBILE2, PhoneNumberFormat::E164, '8.13.38'],

            ['33 1234 5678', self::MX_NUMBER1, PhoneNumberFormat::NATIONAL, '8.10.23'],
            ['+52 33 1234 5678', self::MX_NUMBER1, PhoneNumberFormat::INTERNATIONAL],
            ['+523312345678', self::MX_NUMBER1, PhoneNumberFormat::E164],

            ['821 123 4567', self::MX_NUMBER2, PhoneNumberFormat::NATIONAL, '8.10.23'],
            ['+52 821 123 4567', self::MX_NUMBER2, PhoneNumberFormat::INTERNATIONAL],
            ['+528211234567', self::MX_NUMBER2, PhoneNumberFormat::E164]
        ];
    }

    #[DataProvider('providerFormatForCallingFrom')]
    public function testFormatForCallingFrom(string $phoneNumber, string $countryCode, string $expected) : void
    {
        self::assertSame($expected, PhoneNumber::parse($phoneNumber)->formatForCallingFrom($countryCode));
    }

    public static function providerFormatForCallingFrom() : array
    {
        return [
            ['+33123456789', 'FR', '01 23 45 67 89'],
            ['+33123456789', 'BE', '00 33 1 23 45 67 89'],
            ['+33123456789', 'CH', '00 33 1 23 45 67 89'],
            ['+33123456789', 'DE', '00 33 1 23 45 67 89'],
            ['+33123456789', 'GB', '00 33 1 23 45 67 89'],
            ['+33123456789', 'US', '011 33 1 23 45 67 89'],
            ['+33123456789', 'CA', '011 33 1 23 45 67 89'],
            ['+16502530000', 'US', '1 (650) 253-0000'],
            ['+16502530000', 'CA', '1 (650) 253-0000'],
            ['+16502530000', 'FR', '00 1 650-253-0000'],
            ['+16502530000', 'BE', '00 1 650-253-0000'],
            ['+16502530000', 'CH', '00 1 650-253-0000'],
            ['+16502530000', 'DE', '00 1 650-253-0000'],
            ['+16502530000', 'GB', '00 1 650-253-0000'],
        ];
    }

    #[DataProvider('providerFormatForMobileDialing')]
    public function testFormatForMobileDialing(
        string $phoneNumber,
        string $regionCallingFrom,
        bool $withFormatting,
        ?string $expected,
        ?string $minimumUpstreamVersion = null,
    ) : void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        $actual = PhoneNumber::parse($phoneNumber)->formatForMobileDialing($regionCallingFrom, $withFormatting);
        self::assertSame($expected, $actual);
    }

    public static function providerFormatForMobileDialing() : array
    {
        return [
            ['+33123456789', 'FR', false, '0123456789'],
            ['+33123456789', 'FR', true, '01 23 45 67 89'],
            ['+33123456789', 'BE', false, '+33123456789'],
            ['+33123456789', 'BE', true, '+33 1 23 45 67 89'],
            ['+33123456789', 'US', false, '+33123456789'],
            ['+33123456789', 'US', true, '+33 1 23 45 67 89'],
            ['+33123456789', 'CA', false, '+33123456789'],
            ['+33123456789', 'CA', true, '+33 1 23 45 67 89'],
            ['+558001234567', 'CN', false, null, '8.12.51'],
            ['+558001234567', 'CN', true, null, '8.12.51'],
        ];
    }

    #[DataProvider('providerGetGeographicalAreaCode')]
    public function testGetGeographicalAreaCode(string $phoneNumber, string $areaCode) : void
    {
        self::assertSame($areaCode, PhoneNumber::parse($phoneNumber)->getGeographicalAreaCode());
    }

    public static function providerGetGeographicalAreaCode() : array
    {
        return [
            ['+442079460585', '20'],
            ['+441132224444', '113'],
            ['+447553840000', ''],
            ['+33123000000', '1'],
            ['+33234000000', '2'],
            ['+33345000000', '3'],
            ['+33456000000', '4'],
            ['+33567000000', '5'],
        ];
    }

    #[DataProvider('providerIsEqualTo')]
    public function testIsEqualTo(string $phoneNumber1, string $phoneNumber2, bool $isEqual): void
    {
        $phoneNumber1 = PhoneNumber::parse($phoneNumber1);
        $phoneNumber2 = PhoneNumber::parse($phoneNumber2);

        self::assertSame($isEqual, $phoneNumber1->isEqualTo($phoneNumber2));
    }

    public static function providerIsEqualTo(): array
    {
        return [
            ['+442079460585', '+442079460585', true],
            ['+442079460585', '+442079460586', false],
        ];
    }

    public function testJsonSerializable(): void
    {
        $data = [
            'phoneNumber' => PhoneNumber::parse('0123000000', 'FR')
        ];

        self::assertSame('{"phoneNumber":"+33123000000"}', json_encode($data));
    }

    /**
     * The data provider may provide several possible results, as the results differ depending on the version of the
     * underlying phonenumber library.
     *
     * @param (string|null)[] $expected
     */
    #[DataProvider('providerGetDescription')]
    public function testGetDescription(string $phoneNumber, string $locale, ?string $userRegion, array $expected) : void
    {
        self::assertContains(PhoneNumber::parse($phoneNumber)->getDescription($locale, $userRegion), $expected);
    }

    public static function providerGetDescription() : array
    {
        return [
            ['+16509036313', 'EN', null, ['Mountain View, CA']],
            ['+16509036313', 'EN', 'US', ['Mountain View, CA']],
            ['+16509036313', 'EN', 'GB', ['United States']],
            ['+16509036313', 'EN', 'FR', ['United States']],
            ['+16509036313', 'EN', 'XX', ['United States']],
            ['+16509036313', 'FR', null, ['Mountain View, CA']],
            ['+16509036313', 'FR', 'US', ['Mountain View, CA']],
            ['+16509036313', 'FR', 'GB', ['États-Unis']],
            ['+16509036313', 'FR', 'FR', ['États-Unis']],
            ['+16509036313', 'FR', 'XX', ['États-Unis']],

            ['+33381251234', 'FR', null, ['France', 'Besançon']],
            ['+33381251234', 'FR', 'FR', ['France', 'Besançon']],
            ['+33381251234', 'FR', 'US', ['France']],
            ['+33381251234', 'FR', 'XX', ['France']],
            ['+33381251234', 'EN', null, ['France', 'Besançon']],
            ['+33381251234', 'EN', 'FR', ['France', 'Besançon']],
            ['+33381251234', 'EN', 'US', ['France']],
            ['+33381251234', 'EN', 'XX', ['France']],

            ['+33328201234', 'FR', null, ['France', 'Dunkerque']],
            ['+33328201234', 'FR', 'FR', ['France', 'Dunkerque']],
            ['+33328201234', 'FR', 'US', ['France']],
            ['+33328201234', 'FR', 'XX', ['France']],
            ['+33328201234', 'GB', null, ['Dunkirk', null]],
            ['+33328201234', 'XX', null, ['Dunkirk', null]],

            ['+41229097000', 'FR', null, ['Genève']],
            ['+41229097000', 'FR', 'CH', ['Genève']],
            ['+41229097000', 'FR', 'US', ['Suisse']],
            ['+41229097000', 'XX', null, ['Geneva']],

            ['+37328000000', 'XX', null, [null]],
        ];
    }

    #[DataProvider('providerGetCarrierName')]
    public function testGetCarrierName(
        string $phoneNumber,
        string $languageCode,
        CarrierNameMode $mode,
        ?string $expectedCarrierName,
        ?string $minimumUpstreamVersion = null,
    ): void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        $carrierName = PhoneNumber::parse($phoneNumber)->getCarrierName($languageCode, $mode);
        self::assertSame($expectedCarrierName, $carrierName);
    }

    public static function providerGetCarrierName(): array
    {
        return [
            ['+33600012345', 'en', CarrierNameMode::ALWAYS, 'Free Mobile', '8.11.1'],
            ['+33600012345', 'fr', CarrierNameMode::ALWAYS, 'Free Mobile', '8.11.1'],
            ['+33600012345', 'fr', CarrierNameMode::MOBILE_ONLY, 'Free Mobile', '8.11.1'],
            ['+33600012345', 'fr', CarrierNameMode::MOBILE_NO_PORTABILITY_ONLY, null], // France supports portability
            ['+33900000000', 'fr', CarrierNameMode::ALWAYS, null],
            ['+447305123456', 'en', CarrierNameMode::ALWAYS, 'Virgin Mobile', '8.0.1'],
            ['+447305123456', 'fr', CarrierNameMode::ALWAYS, 'Virgin Mobile', '8.0.1'],
            ['+821001234567', 'en', CarrierNameMode::ALWAYS, 'LG U+', '8.13.17'],
            ['+821001234567', 'fr', CarrierNameMode::ALWAYS, 'LG U+', '8.13.17'],
            ['+821001234567', 'ko', CarrierNameMode::ALWAYS, '데이콤', '8.13.17'],
        ];
    }

    /**
     * @param string[] $expectedTimeZones
     */
    #[DataProvider('providerGetTimeZones')]
    public function testGetTimeZones(
        string $phoneNumber,
        array $expectedTimeZones,
        ?string $minimumUpstreamVersion = null,
    ): void {
        if ($minimumUpstreamVersion !== null) {
            self::requireUpstreamVersion($minimumUpstreamVersion);
        }

        $timeZones = PhoneNumber::parse($phoneNumber)->getTimeZones();
        self::assertSame($expectedTimeZones, $timeZones);
    }

    public static function providerGetTimeZones(): array
    {
        return [
            ['+33600012345', ['Europe/Paris']],
            ['+441614960000', ['Europe/London']],
            ['+4412', []],
            ['+447123456789', [
                'Europe/Guernsey',
                'Europe/Isle_of_Man',
                'Europe/Jersey',
                'Europe/London',
            ], '8.10.23'],
        ];
    }

    public static function requireUpstreamVersion(string $version): void
    {
        $packageName = 'giggsey/libphonenumber-for-php';
        $installedVersion = InstalledVersions::getVersion($packageName);

        if (version_compare($installedVersion, $version, '<')) {
            self::markTestSkipped(sprintf('This test requires %s version %s or later.', $packageName, $version));
        }
    }
}
