<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * A uniqueness invariant would be violated.
 *
 * Translated into a 409 at the HTTP boundary (config/packages/api_platform.yaml).
 */
abstract class AlreadyExistException extends DomainException
{
}
