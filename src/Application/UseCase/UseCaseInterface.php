<?php

declare(strict_types=1);

namespace App\Application\UseCase;

/**
 * Contract of every use case (ADR 0003).
 *
 * A use case lives in Application/UseCase/<Feature>/<Action>/ next to its
 * <Action>Request and <Action>Response. It never returns null: a missing
 * result is a NotFoundException. Each implementation declares the business
 * exceptions it can throw with @throws (checked by PHPStan, ADR 0009).
 *
 * @template TRequest of UseCaseRequestInterface
 * @template TResponse of UseCaseResponseInterface
 */
interface UseCaseInterface
{
    /**
     * @param TRequest $request
     *
     * @return TResponse
     */
    public function execute(UseCaseRequestInterface $request): UseCaseResponseInterface;
}
