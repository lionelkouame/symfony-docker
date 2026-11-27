<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\Resource\System;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\Infrastructure\Presentation\ApiPlatform\V1\State\Provider\System\HealthCheckProvider;

/**
 * A resource only declares operations; it holds no data and no logic (ADR 0004).
 */
#[ApiResource(
    shortName: 'HealthCheck',
    operations: [
        new Get(
            uriTemplate: '/health',
            openapi: new Operation(
                tags: self::TAGS,
                summary: 'Check the API health (V1)',
                description: 'Lightweight endpoint crossing every Clean Architecture layer.',
            ),
            output: HealthCheckOutput::class,
            provider: HealthCheckProvider::class,
        ),
    ],
    routePrefix: '/v1',
)]
final class HealthCheckResource
{
    public const array TAGS = ['V1 - System'];
}
