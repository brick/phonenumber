<?php

declare(strict_types=1);

namespace Brick\PhoneNumber\Tests;

use BackedEnum;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberType;

use PHPUnit\Framework\TestCase;

/**
 * Tests that enums are up-to-date with libphonenumber constants.
 */
class EnumTest extends TestCase
{
    /**
     * @param class-string             $classExpected   The name or the reference libphonenumber class.
     * @param class-string<BackedEnum> $enumClassActual The name of the enum class to test against the reference class.
     */
    private static function assertEnumEqualsConstants(string $classExpected, string $enumClassActual) : void
    {
        $expected = (new \ReflectionClass($classExpected))->getConstants();

        $actual = [];

        foreach ($enumClassActual::cases() as $enum) {
            $actual[$enum->name] = $enum->value;
        }

        self::assertSame($expected, $actual);
    }

    public function testPhoneNumberFormats() : void
    {
        self::assertEnumEqualsConstants(\libphonenumber\PhoneNumberFormat::class, PhoneNumberFormat::class);
    }

    public function testPhoneNumberTypes() : void
    {
        self::assertEnumEqualsConstants(\libphonenumber\PhoneNumberType::class, PhoneNumberType::class);
    }
}
