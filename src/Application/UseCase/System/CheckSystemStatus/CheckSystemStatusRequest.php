<?php

declare(strict_types=1);

namespace App\Application\UseCase\System\CheckSystemStatus;

use App\Application\UseCase\UseCaseRequestInterface;

/**
 * Empty on purpose: a use case without parameters still receives a request, so
 * that criteria can be added later without changing its signature.
 */
final readonly class CheckSystemStatusRequest implements UseCaseRequestInterface
{
}
