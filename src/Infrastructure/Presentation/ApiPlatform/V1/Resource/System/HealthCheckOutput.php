<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\Resource\System;

use App\Domain\ReadModel\System\SystemStatus;

/**
 * HTTP representation of the system status: primitives only.
 */
final readonly class HealthCheckOutput
{
    public function __construct(
        public string $status,
        public string $version,
        public string $environment,
        public string $timestamp,
    ) {}

    public static function fromReadModel(SystemStatus $readModel): self
    {
        return new self(
            status: $readModel->status,
            version: $readModel->version,
            environment: $readModel->environment,
            timestamp: $readModel->timestamp->format(\DateTimeInterface::ATOM),
        );
    }
}
