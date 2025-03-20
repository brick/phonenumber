<?php

declare(strict_types=1);

namespace Brick\PhoneNumber\Tests;

use BackedEnum;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseErrorType;
use Brick\PhoneNumber\PhoneNumberType;
use PHPUnit\Framework\TestCase;

/**
 * Tests that enums are up-to-date with libphonenumber constants.
 */
class EnumTest extends TestCase
{
    /**
     * @param class-string<BackedEnum> $enumClassExpected The name or the reference libphonenumber enum class.
     * @param class-string<BackedEnum> $enumClassActual   The name of the enum class to test against the reference class.
     */
    private static function assertEnumEqualsEnum(string $enumClassExpected, string $enumClassActual) : void
    {
        self::assertSame(
            self::enumToMap($enumClassExpected),
            self::enumToMap($enumClassActual),
        );
    }

    /**
     * @param class-string             $classExpected   The name or the reference libphonenumber class.
     * @param class-string<BackedEnum> $enumClassActual The name of the enum class to test against the reference class.
     */
    private static function assertEnumEqualsConstants(string $classExpected, string $enumClassActual) : void
    {
        $expected = (new \ReflectionClass($classExpected))->getConstants();
        $actual = self::enumToMap($enumClassActual);

        self::assertSame($expected, $actual);
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     *
     * @return array<string, int|string>
     */
    private static function enumToMap(string $enumClass): array
    {
        $values = [];

        foreach ($enumClass::cases() as $enum) {
            $values[$enum->name] = $enum->value;
        }

        return $values;
    }

    public function testPhoneNumberFormats() : void
    {
        self::assertEnumEqualsEnum(\libphonenumber\PhoneNumberFormat::class, PhoneNumberFormat::class);
    }

    public function testPhoneNumberTypes() : void
    {
        self::assertEnumEqualsEnum(\libphonenumber\PhoneNumberType::class, PhoneNumberType::class);
    }

    public function testPhoneNumberParseErrorTypes() : void
    {
        self::assertEnumEqualsConstants(\libphonenumber\NumberParseException::class, PhoneNumberParseErrorType::class);
    }
}
