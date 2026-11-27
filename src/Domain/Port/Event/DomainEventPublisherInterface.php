<?php

declare(strict_types=1);

namespace App\Domain\Port\Event;

use App\Domain\Event\DomainEvent;

/**
 * Port used by use cases to publish domain events (ADR 0013).
 *
 * Use cases depend on this abstraction only, never on Symfony's or PSR-14's
 * event dispatcher. The adapter lives in Infrastructure/Event/<Technology>/.
 */
interface DomainEventPublisherInterface
{
    public function publish(DomainEvent $event): void;
}
