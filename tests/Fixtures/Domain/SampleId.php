<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Domain;

use App\Domain\ValueObject\IdentifierInterface;
use App\Domain\ValueObject\IdentifierTrait;

/**
 * Test-only identifier, shaped like the real ones (OrderId, CustomerId...).
 */
final readonly class SampleId implements IdentifierInterface
{
    use IdentifierTrait;
}
