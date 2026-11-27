<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity\SymfonyUid;

use App\Domain\Port\Identity\IdValidatorInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SymfonyUuidValidator implements IdValidatorInterface
{
    public function isValid(string $id): bool
    {
        return Uuid::isValid($id);
    }
}
