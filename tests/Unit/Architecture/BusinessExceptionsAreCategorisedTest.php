<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use App\Domain\Exception\AlreadyExistException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\InvalidDataException;
use App\Domain\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Only the categories below are translated into an HTTP status
 * (config/packages/api_platform.yaml). A concrete business exception that
 * extends none of them would surface as a 500 (ADR 0009).
 *
 * Adding a category = mapping it in api_platform.yaml AND listing it here.
 */
final class BusinessExceptionsAreCategorisedTest extends TestCase
{
    /** @var list<class-string<DomainException>> */
    private const array MAPPED_CATEGORIES = [
        NotFoundException::class,
        AlreadyExistException::class,
        InvalidDataException::class,
    ];

    public function testEveryConcreteBusinessExceptionExtendsAMappedCategory(): void
    {
        $directory = \dirname(__DIR__, 3).'/src/Domain/Exception';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

        $checked = 0;
        $offenders = [];

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || 'php' !== $file->getExtension()) {
                continue;
            }

            $relative = substr($file->getPathname(), \strlen($directory) + 1, -4);
            $class = 'App\\Domain\\Exception\\'.str_replace('/', '\\', $relative);

            if (!class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            ++$checked;
            $mapped = array_filter(self::MAPPED_CATEGORIES, static fn (string $category): bool => is_a($class, $category, true));
            if ([] === $mapped) {
                $offenders[] = $class;
            }
        }

        self::assertGreaterThan(0, $checked, 'No concrete business exception found: is the directory right?');
        self::assertSame([], $offenders, 'Extend NotFoundException, AlreadyExistException or InvalidDataException (ADR 0009).');
    }

    public function testTheRootBusinessExceptionCannotBeThrownDirectly(): void
    {
        self::assertTrue((new \ReflectionClass(DomainException::class))->isAbstract());
    }
}
