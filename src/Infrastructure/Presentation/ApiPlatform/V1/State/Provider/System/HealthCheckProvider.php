<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Application\UseCase\System\CheckSystemStatus\CheckSystemStatusRequest;
use App\Application\UseCase\System\CheckSystemStatus\CheckSystemStatusUseCase;
use App\Infrastructure\Presentation\ApiPlatform\V1\Resource\System\HealthCheckOutput;

/**
 * Thin adapter: builds the Request, calls the use case, maps the read model to
 * the Output. No business decision and no try/catch (ADR 0004, ADR 0009).
 *
 * @implements ProviderInterface<HealthCheckOutput>
 */
final readonly class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private CheckSystemStatusUseCase $useCase,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HealthCheckOutput
    {
        $response = $this->useCase->execute(new CheckSystemStatusRequest());

        return HealthCheckOutput::fromReadModel($response->readModel);
    }
}
