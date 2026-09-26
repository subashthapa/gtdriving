<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductionDeploymentConfigTest extends TestCase
{
    public function test_database_volume_does_not_hide_application_migrations(): void
    {
        $root = dirname(__DIR__, 2);
        $compose = file_get_contents($root.'/compose.production.yaml');
        $environment = file_get_contents($root.'/deploy/.env.example');
        $entrypoint = file_get_contents($root.'/deploy/docker/entrypoint.sh');

        $this->assertNotFalse($compose);
        $this->assertNotFalse($environment);
        $this->assertNotFalse($entrypoint);
        $this->assertStringContainsString(
            'gtdriving_database:/var/lib/gtdriving',
            $compose
        );
        $this->assertStringNotContainsString(
            'gtdriving_database:/var/www/html/database',
            $compose
        );
        $this->assertStringContainsString(
            'DB_DATABASE=/var/lib/gtdriving/database.sqlite',
            $environment
        );
        $this->assertStringContainsString(
            '${DB_DATABASE:-/var/lib/gtdriving/database.sqlite}',
            $entrypoint
        );
    }
}
