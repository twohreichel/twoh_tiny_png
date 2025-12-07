<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use TWOH\TwohTinyPng\Domain\Service\BulkService;

final class BulkServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/tinypng_test_' . uniqid('', true);
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/sub');
        // Fake files
        touch($this->tempDir . '/image1.jpg');
        touch($this->tempDir . '/image2.png');
        touch($this->tempDir . '/ignore.txt');
        touch($this->tempDir . '/sub/nested.jpeg');
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tempDir);
    }

    private function deleteDir(string $dir): void
    {
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $path = "$dir/$f";
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testScanAllDirFindsImagesOnly(): void
    {
        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertCount(3, $files);
        self::assertContains('image1.jpg', $files);
        self::assertContains('image2.png', $files);
        self::assertContains('sub/nested.jpeg', $files);
    }
}
