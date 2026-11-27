<?php

declare(strict_types=1);

namespace App\Application\UseCase;

/**
 * Input of a use case: a readonly object carrying primitives only.
 *
 * The use case turns them into Value Objects (ADR 0005).
 */
interface UseCaseRequestInterface
{
}
