<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class PublicDocumentationSecurityTest extends TestCase
{
    /**
     * User-facing Markdown belongs at the repository root or in docs/public.
     * Add other intentionally public locations here before publishing them.
     *
     * @return array<string, array{string}>
     */
    public static function publicDocumentProvider(): array
    {
        $root = dirname(__DIR__, 2);
        $documents = [];

        foreach (['README.md', 'USER_GUIDE.md'] as $filename) {
            $path = $root.DIRECTORY_SEPARATOR.$filename;

            if (is_file($path)) {
                $documents[$filename] = [$path];
            }
        }

        $publicDocs = $root.DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'public';

        if (is_dir($publicDocs)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($publicDocs));

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
                    $relativePath = substr($file->getPathname(), strlen($root) + 1);
                    $documents[$relativePath] = [$file->getPathname()];
                }
            }
        }

        return $documents;
    }

    #[DataProvider('publicDocumentProvider')]
    public function test_public_documentation_excludes_sensitive_and_operational_details(string $path): void
    {
        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);

        $prohibitedPatterns = [
            'personal or privileged email address' => '/\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/i',
            'environment-specific website address' => '/https?:\/\/(?:www\.)?gtdriving\.com\.au\b/i',
            'privileged role identifier' => '/(?:`admin`|\bsuper[\s_-]*admin\b|\badministrator role\b)/i',
            'direct administration path' => '/(?:https?:\/\/[^\s)]+)?\/(?:dashboard\/)?admin(?:\/|\b)/i',
            'credential or secret value' => '/\b(?:password|passphrase|secret|token|api[_ -]?key|recovery code)\s*[:=]\s*[`"\']?[^\s`"\']+/i',
            'destructive staff procedure' => '/\b(?:delete|remove|revoke|refund)\b.{0,80}\b(?:account|user|package|page|message|record|invitation|payment)\b/i',
            'production operation' => '/\b(?:production database|production server|deployment|rollback|server administration)\b/i',
        ];

        foreach ($prohibitedPatterns as $description => $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $contents,
                basename($path).' exposes a '.$description.'. Move operational guidance to private documentation.'
            );
        }
    }
}
