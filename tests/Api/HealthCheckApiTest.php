<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;

/**
 * End-to-end check of the template: HTTP → Provider → UseCase → read model → Output.
 */
final class HealthCheckApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testItReportsTheRealEnvironmentAndVersion(): void
    {
        $data = static::createClient()->request('GET', '/api/v1/health', [
            'headers' => ['Accept' => 'application/json'],
        ])->toArray();

        self::assertResponseStatusCodeSame(200);
        self::assertSame('OK', $data['status']);
        self::assertSame('test', $data['environment']);
        self::assertSame(self::getContainer()->getParameter('app.version'), $data['version']);
        self::assertIsString($data['timestamp']);
        self::assertNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data['timestamp']));
    }

    public function testTheOpenApiDocumentationExposesTheHealthCheck(): void
    {
        $data = static::createClient()->request('GET', '/api/docs', [
            'headers' => ['Accept' => 'application/vnd.openapi+json'],
        ])->toArray();

        self::assertResponseIsSuccessful();
        self::assertIsArray($data['paths']);
        self::assertArrayHasKey('/api/v1/health', $data['paths']);
    }
}
