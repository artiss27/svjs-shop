<?php

namespace App\Tests\Unit\Generator;

use App\Utils\Generator\PasswordGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class PasswordGeneratorTest extends TestCase
{
    public function testGeneratePassword(): void
    {
        $password = PasswordGenerator::generatePassword(8);
        self::assertSame(8, strlen($password));
    }
}
