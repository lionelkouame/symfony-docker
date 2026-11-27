<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Identity\SymfonyUid;

use App\Infrastructure\Identity\SymfonyUid\SymfonyUuidGenerator;
use App\Infrastructure\Identity\SymfonyUid\SymfonyUuidValidator;
use PHPUnit\Framework\TestCase;

final class SymfonyUuidGeneratorTest extends TestCase
{
    public function testItGeneratesDistinctValidIdentifiers(): void
    {
        $generator = new SymfonyUuidGenerator();
        $validator = new SymfonyUuidValidator();

        $first = $generator->generate();
        $second = $generator->generate();

        self::assertTrue($validator->isValid($first));
        self::assertNotSame($first, $second);
    }

    public function testTheValidatorRejectsAMalformedIdentifier(): void
    {
        self::assertFalse(new SymfonyUuidValidator()->isValid('not-a-uuid'));
    }
}
