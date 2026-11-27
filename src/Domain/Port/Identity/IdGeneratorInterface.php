<?php

declare(strict_types=1);

namespace App\Domain\Port\Identity;

/**
 * Port generating unique identifiers for new aggregates.
 *
 * The Domain does not know how identifiers are produced (UUID v7, ULID...):
 * the adapter lives in Infrastructure/Identity/<Technology>/.
 */
interface IdGeneratorInterface
{
    public function generate(): string;
}
