<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Development fixtures. They live in Infrastructure/Persistence/Doctrine because
 * they depend on Doctrine; build Domain entities through their named
 * constructors, never by bypassing their invariants.
 */
final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
