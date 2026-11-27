<?php

declare(strict_types=1);

namespace App\Domain\Port\Identity;

/**
 * Port checking that a string is a well-formed identifier, so that an
 * identifier coming from the outside can be rejected before any lookup.
 */
interface IdValidatorInterface
{
    public function isValid(string $id): bool;
}
