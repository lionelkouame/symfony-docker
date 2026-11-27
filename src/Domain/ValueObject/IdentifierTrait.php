<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidValueException;
use App\Domain\Port\Identity\IdValidatorInterface;

/**
 * Implementation of {@see IdentifierInterface}, shared by every identifier Value Object.
 *
 *     final readonly class OrderId implements IdentifierInterface
 *     {
 *         use IdentifierTrait;
 *     }
 *
 * Built with OrderId::make($value), or OrderId::make($value, $validator) when the
 * value comes from the outside and must be checked first.
 */
trait IdentifierTrait
{
    /**
     * @throws InvalidValueException when the identifier is empty or malformed
     */
    private function __construct(
        private readonly string $value,
        ?IdValidatorInterface $validator = null,
    ) {
        if ('' === trim($this->value)) {
            throw new InvalidValueException('The identifier cannot be empty.');
        }

        if (null !== $validator && !$validator->isValid($this->value)) {
            throw new InvalidValueException(\sprintf('The identifier "%s" is not valid.', $this->value));
        }
    }

    /**
     * @throws InvalidValueException when the identifier is empty or malformed
     */
    public static function make(string $value, ?IdValidatorInterface $validator = null): self
    {
        return new self($value, $validator);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
