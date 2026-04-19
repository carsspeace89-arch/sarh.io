<?php
// =============================================================
// tests/Integration/ApiOriginValidationIntegrationTest.php
// =============================================================

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class ApiOriginValidationIntegrationTest extends TestCase
{
    private function runSnippet(string $snippet): string
    {
        $cmd = PHP_BINARY . ' -r ' . escapeshellarg($snippet);
        return trim((string)shell_exec($cmd));
    }

    public function testAllowsSameOrigin(): void
    {
        $root = dirname(__DIR__, 2);
        $snippet = <<<'PHP'
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/api/check-out.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_ORIGIN'] = 'https://example.test';
require 'ROOT/includes/functions.php';
echo validateApiOrigin() ? '1' : '0';
PHP;
        $snippet = str_replace('ROOT', addslashes($root), $snippet);

        $out = $this->runSnippet($snippet);
        $this->assertSame('1', $out);
    }

    public function testBlocksCrossOrigin(): void
    {
        $root = dirname(__DIR__, 2);
        $snippet = <<<'PHP'
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/api/check-out.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_ORIGIN'] = 'https://evil.example';
require 'ROOT/includes/functions.php';
echo validateApiOrigin() ? '1' : '0';
PHP;
        $snippet = str_replace('ROOT', addslashes($root), $snippet);

        $out = $this->runSnippet($snippet);
        $this->assertSame('0', $out);
    }

    public function testAllowsRequestsWithoutOriginAndReferer(): void
    {
        $root = dirname(__DIR__, 2);
        $snippet = <<<'PHP'
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/api/check-out.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
require 'ROOT/includes/functions.php';
echo validateApiOrigin() ? '1' : '0';
PHP;
        $snippet = str_replace('ROOT', addslashes($root), $snippet);

        $out = $this->runSnippet($snippet);
        $this->assertSame('1', $out);
    }
}
