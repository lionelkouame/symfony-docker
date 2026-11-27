<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidValueException;
use App\Infrastructure\Identity\SymfonyUid\SymfonyUuidValidator;
use App\Tests\Fixtures\Domain\SampleId;
use PHPUnit\Framework\TestCase;

final class IdentifierTraitTest extends TestCase
{
    public function testItExposesItsValue(): void
    {
        $id = SampleId::make('0193a4c2-7d2e-7b8a-9c4f-3e2d1c0b9a87');

        self::assertSame('0193a4c2-7d2e-7b8a-9c4f-3e2d1c0b9a87', $id->value());
        self::assertSame('0193a4c2-7d2e-7b8a-9c4f-3e2d1c0b9a87', (string) $id);
    }

    public function testTwoIdentifiersWithTheSameValueAreEqual(): void
    {
        self::assertTrue(SampleId::make('a')->equals(SampleId::make('a')));
        self::assertFalse(SampleId::make('a')->equals(SampleId::make('b')));
    }

    public function testAnEmptyIdentifierIsRejected(): void
    {
        $this->expectException(InvalidValueException::class);

        SampleId::make('  ');
    }

    public function testAMalformedIdentifierIsRejectedWhenAValidatorIsGiven(): void
    {
        $this->expectException(InvalidValueException::class);

        SampleId::make('not-a-uuid', new SymfonyUuidValidator());
    }
}
