<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * The requested aggregate does not exist (or the identifier cannot match anything).
 *
 * Translated into a 404 at the HTTP boundary (config/packages/api_platform.yaml).
 */
abstract class NotFoundException extends DomainException
{
}
