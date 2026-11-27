<?php

declare(strict_types=1);

namespace App\Domain\ReadModel\System;

/**
 * Read-only projection returned by CheckSystemStatusUseCase.
 *
 * A read model is what a use case returns instead of an entity: the entity
 * never leaves the Application layer (ADR 0003).
 */
final readonly class SystemStatus
{
    public function __construct(
        public string $status,
        public string $version,
        public string $environment,
        public \DateTimeImmutable $timestamp,
    ) {}
}
