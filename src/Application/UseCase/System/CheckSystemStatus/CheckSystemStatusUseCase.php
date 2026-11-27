<?php

declare(strict_types=1);

namespace App\Application\UseCase\System\CheckSystemStatus;

use App\Application\UseCase\UseCaseInterface;
use App\Application\UseCase\UseCaseRequestInterface;
use App\Domain\ReadModel\System\SystemStatus;

/**
 * Reference use case of the template: it crosses every layer
 * (Presentation → Application → Domain read model) and backs GET /api/v1/health.
 *
 * @implements UseCaseInterface<CheckSystemStatusRequest, CheckSystemStatusResponse>
 */
final readonly class CheckSystemStatusUseCase implements UseCaseInterface
{
    public const string STATUS_OK = 'OK';

    /**
     * Both values are injected by the container (config/services.yaml). No
     * default here, so a missing configuration fails loudly instead of
     * reporting a wrong environment.
     */
    public function __construct(
        private string $environment,
        private string $version,
    ) {}

    public function execute(UseCaseRequestInterface $request): CheckSystemStatusResponse
    {
        return new CheckSystemStatusResponse(new SystemStatus(
            status: self::STATUS_OK,
            version: $this->version,
            environment: $this->environment,
            timestamp: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        ));
    }
}
