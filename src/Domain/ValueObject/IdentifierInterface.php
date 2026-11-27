<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Contract shared by every aggregate identifier (OrderId, CustomerId...).
 *
 * Implement it with {@see IdentifierTrait}.
 */
interface IdentifierInterface extends \Stringable
{
    public function value(): string;
}
