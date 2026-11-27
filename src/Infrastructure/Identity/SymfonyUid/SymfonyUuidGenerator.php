<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity\SymfonyUid;

use App\Domain\Port\Identity\IdGeneratorInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Generates time-ordered UUIDs (v7), which keep database indexes compact.
 */
final readonly class SymfonyUuidGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
