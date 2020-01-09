<?php

declare(strict_types=1);

namespace Brick\PhoneNumber\Tests;

use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberType;

use PHPUnit\Framework\TestCase;

/**
 * Tests that the constants are up-to-date with libphonenumber.
 */
class ConstantTest extends TestCase
{
    /**
     * Compares the constants of two classes.
     *
     * @param string $classExpected The name or the reference class.
     * @param string $classActual   The name of the class to test against the reference class.
     */
    private function assertConstantsEqual(string $classExpected, string $classActual) : void
    {
        $expected = new \ReflectionClass($classExpected);
        $actual   = new \ReflectionClass($classActual);

        $this->assertSame($expected->getConstants(), $actual->getConstants());
    }

    public function testPhoneNumberFormats() : void
    {
        $this->assertConstantsEqual(\libphonenumber\PhoneNumberFormat::class, PhoneNumberFormat::class);
    }

    public function testPhoneNumberTypes() : void
    {
        $this->assertConstantsEqual(\libphonenumber\PhoneNumberType::class, PhoneNumberType::class);
    }
}
