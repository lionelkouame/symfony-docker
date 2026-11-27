<?php

declare(strict_types=1);

namespace App\Infrastructure\Event\Symfony;

use App\Domain\Event\DomainEvent;
use App\Domain\Port\Event\DomainEventPublisherInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Adapter of the {@see DomainEventPublisherInterface} port onto Symfony's
 * PSR-14 event dispatcher.
 *
 * Events are dispatched as is: listeners (Infrastructure/Event/Symfony/Listener/<Feature>/)
 * subscribe to their concrete class with #[AsEventListener].
 */
final readonly class SymfonyDomainEventPublisher implements DomainEventPublisherInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function publish(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
