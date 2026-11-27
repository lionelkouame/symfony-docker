<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * A value does not satisfy a Value Object's invariant.
 *
 * Concrete on purpose: Value Objects throw it directly instead of declaring one
 * exception class each. The generic SPL argument exception is forbidden in the
 * Domain and Application layers (architecture test in tests/Unit/Architecture/).
 */
final class InvalidValueException extends InvalidDataException
{
}
