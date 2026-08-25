<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PackageBoundaryTest extends TestCase
{
    public function testServerPackageHasNoFrontendDependency(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(__DIR__.'/../composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $requirements = array_keys((array) ($manifest['require'] ?? []));

        self::assertNotContains('pushinbr/pam-native', $requirements);
        self::assertNotContains('pushinbr/pam-native-sync', $requirements);
        self::assertSame('library', $manifest['type'] ?? null);
        self::assertArrayNotHasKey('pam-native', (array) ($manifest['extra'] ?? []));
    }

    public function testServerSourceDoesNotImportFrontendNamespaces(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../src', FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('Pam\\Native\\Sync\\', $source, $file->getPathname());
            self::assertStringNotContainsString('Pam\\Native\\Http\\', $source, $file->getPathname());
            self::assertStringNotContainsString('Pam\\Native\\Plugin\\', $source, $file->getPathname());
        }
    }
}
