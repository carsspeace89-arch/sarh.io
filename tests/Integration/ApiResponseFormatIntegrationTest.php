<?php
// =============================================================
// tests/Integration/ApiResponseFormatIntegrationTest.php
// =============================================================

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class ApiResponseFormatIntegrationTest extends TestCase
{
    private function runSnippet(string $snippet): string
    {
        $cmd = PHP_BINARY . ' -r ' . escapeshellarg($snippet);
        return (string)shell_exec($cmd);
    }

    public function testApiSuccessEnvelopeIsUnified(): void
    {
        $root = dirname(__DIR__, 2);
        $snippet = <<<'PHP'
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/api/check-in.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
require 'ROOT/includes/functions.php';
apiSuccess(['message' => 'ok', 'foo' => 'bar']);
PHP;
        $snippet = str_replace('ROOT', addslashes($root), $snippet);

        $out = $this->runSnippet($snippet);
        $json = json_decode($out, true);

        $this->assertIsArray($json);
        $this->assertTrue($json['success'] ?? false);
        $this->assertSame('ok', $json['message'] ?? null);
        $this->assertSame('bar', $json['foo'] ?? null);
        $this->assertArrayHasKey('meta', $json);
        $this->assertArrayHasKey('timestamp', $json['meta']);
    }

    public function testApiErrorEnvelopeKeepsBackwardCompatibility(): void
    {
        $root = dirname(__DIR__, 2);
        $snippet = <<<'PHP'
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/api/auth-pin.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
require 'ROOT/includes/functions.php';
apiError('bad request', 400, ['reason' => 'invalid_input']);
PHP;
        $snippet = str_replace('ROOT', addslashes($root), $snippet);

        $out = $this->runSnippet($snippet);
        $json = json_decode($out, true);

        $this->assertIsArray($json);
        $this->assertFalse($json['success'] ?? true);
        $this->assertSame('bad request', $json['message'] ?? null);
        $this->assertArrayHasKey('error', $json);
        $this->assertSame('bad request', $json['error']['message'] ?? null);
        $this->assertSame(400, $json['error']['code'] ?? null);
        $this->assertArrayHasKey('meta', $json);
        $this->assertSame('invalid_input', $json['meta']['reason'] ?? null);
    }
}
