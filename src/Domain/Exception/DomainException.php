<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * Root of every business exception.
 *
 * Abstract on purpose: a business exception must extend one of the categories
 * translated into an HTTP status (NotFoundException, AlreadyExistException,
 * InvalidDataException), otherwise it would surface as a 500 (ADR 0009).
 * Enforced by tests/Unit/Architecture/BusinessExceptionsAreCategorisedTest.
 *
 * Messages are sent to the client and may be logged: never put personal data
 * in them.
 */
abstract class DomainException extends \DomainException
{
}
