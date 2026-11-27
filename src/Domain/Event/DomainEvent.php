<?php

declare(strict_types=1);

namespace App\Domain\Event;

/**
 * Contract of every business event raised by the Domain (ADR 0013).
 *
 * A domain event is an immutable record of something that already happened,
 * hence a past-tense name (OrderPlaced, AccountClosed...). It carries value
 * objects or primitives, never the aggregate itself, so it stays serialisable
 * and free of any framework dependency.
 */
interface DomainEvent
{
    /**
     * When the event occurred (UTC).
     */
    public function occurredAt(): \DateTimeImmutable;
}
