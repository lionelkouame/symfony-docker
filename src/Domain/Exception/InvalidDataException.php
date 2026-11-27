<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * The data, or the requested state transition, breaks a business rule.
 *
 * Translated into a 422 at the HTTP boundary (config/packages/api_platform.yaml).
 */
abstract class InvalidDataException extends DomainException
{
}
