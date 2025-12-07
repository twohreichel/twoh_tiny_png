<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TWOH\TwohTinyPng\Domain\Service\BulkService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(BulkService::class)]
final class BulkServiceTest extends UnitTestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
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
        parent::tearDown();
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

    #[Test]
    public function scanAllDirFindsImagesOnly(): void
    {
        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertCount(3, $files);
        self::assertContains('image1.jpg', $files);
        self::assertContains('image2.png', $files);
        self::assertContains('sub/nested.jpeg', $files);
    }

    #[Test]
    public function scanAllDirIgnoresNonImageFiles(): void
    {
        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertNotContains('ignore.txt', $files);
    }

    #[Test]
    public function scanAllDirReturnsEmptyArrayForEmptyDirectory(): void
    {
        $emptyDir = sys_get_temp_dir() . '/tinypng_empty_' . uniqid('', true);
        mkdir($emptyDir);

        $service = new BulkService();
        $files = $service->scanAllDir($emptyDir);

        self::assertCount(0, $files);
        self::assertIsArray($files);

        rmdir($emptyDir);
    }

    #[Test]
    public function scanAllDirIgnoresHiddenFiles(): void
    {
        touch($this->tempDir . '/.hidden.jpg');

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertNotContains('.hidden.jpg', $files);
    }

    #[Test]
    public function scanAllDirIgnoresProcessedDirectory(): void
    {
        mkdir($this->tempDir . '/_processed_');
        touch($this->tempDir . '/_processed_/processed.jpg');

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertNotContains('_processed_/processed.jpg', $files);
    }

    #[Test]
    public function scanAllDirIgnoresTempDirectory(): void
    {
        mkdir($this->tempDir . '/_temp_');
        touch($this->tempDir . '/_temp_/temp.jpg');

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertNotContains('_temp_/temp.jpg', $files);
    }

    #[Test]
    public function scanAllDirHandlesDeeplyNestedDirectories(): void
    {
        mkdir($this->tempDir . '/level1');
        mkdir($this->tempDir . '/level1/level2');
        mkdir($this->tempDir . '/level1/level2/level3');
        touch($this->tempDir . '/level1/level2/level3/deep.jpg');

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertContains('level1/level2/level3/deep.jpg', $files);
    }

    #[Test]
    #[DataProvider('imageExtensionDataProvider')]
    public function scanAllDirRecognizesValidImageExtensions(string $extension, bool $shouldBeIncluded): void
    {
        $filename = 'testfile.' . $extension;
        touch($this->tempDir . '/' . $filename);

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        if ($shouldBeIncluded) {
            self::assertContains($filename, $files);
        } else {
            self::assertNotContains($filename, $files);
        }
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function imageExtensionDataProvider(): array
    {
        return [
            'jpg extension' => ['jpg', true],
            'jpeg extension' => ['jpeg', true],
            'png extension' => ['png', true],
            'gif extension' => ['gif', false],
            'webp extension' => ['webp', false],
            'svg extension' => ['svg', false],
            'pdf extension' => ['pdf', false],
            'doc extension' => ['doc', false],
        ];
    }

    #[Test]
    public function bulkServiceCanBeInstantiated(): void
    {
        $service = new BulkService();

        self::assertInstanceOf(BulkService::class, $service);
    }

    #[Test]
    public function scanAllDirHandlesMultipleSubdirectories(): void
    {
        mkdir($this->tempDir . '/gallery1');
        mkdir($this->tempDir . '/gallery2');
        touch($this->tempDir . '/gallery1/photo1.jpg');
        touch($this->tempDir . '/gallery2/photo2.png');

        $service = new BulkService();
        $files = $service->scanAllDir($this->tempDir);

        self::assertContains('gallery1/photo1.jpg', $files);
        self::assertContains('gallery2/photo2.png', $files);
    }
}
