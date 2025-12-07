<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Utilities;

use Doctrine\DBAL\Driver\Exception as DriverException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use TWOH\TwohTinyPng\Domain\Service\BulkService;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(MetaDataUtility::class)]
final class MetaDataUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private MockObject&BulkService $bulkServiceMock;
    private MockObject&DatabaseUtility $databaseUtilityMock;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bulkServiceMock = $this->createMock(BulkService::class);
        $this->databaseUtilityMock = $this->createMock(DatabaseUtility::class);

        // Create temp directory for testing
        $this->tempDir = sys_get_temp_dir() . '/tinypng_meta_test_' . uniqid('', true);
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        $this->deleteDir($this->tempDir);
        parent::tearDown();
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
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
    public function metaDataUtilityCanBeInstantiated(): void
    {
        $utility = new MetaDataUtility();

        self::assertInstanceOf(MetaDataUtility::class, $utility);
    }

    #[Test]
    public function bulkMetaDataReturnsFailureWhenNoFilesFound(): void
    {
        $this->bulkServiceMock
            ->method('scanAllDir')
            ->willReturn([]);

        GeneralUtility::addInstance(BulkService::class, $this->bulkServiceMock);

        $utility = new MetaDataUtility();
        $result = $utility->bulkMetaData();

        self::assertSame(Command::FAILURE, $result);
    }

    #[Test]
    public function updateMetaDataHandlesMissingSysFile(): void
    {
        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->with('test/image.jpg')
            ->willReturn([]);

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        // Should not throw any exception
        $utility = new MetaDataUtility();
        $utility->updateMetaData('/var/www/html/fileadmin/', 'test/image.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataUpdatesFileSizeAndDimensions(): void
    {
        // Create a real test image
        $testImagePath = $this->tempDir . '/test.jpg';
        $this->createTestImage($testImagePath);

        $sysFileData = [
            ['uid' => 5, 'identifier' => '/fileadmin/test.jpg'],
        ];

        $sysFileMetaData = [
            ['uid' => 10, 'file' => 5, 'width' => 100, 'height' => 100],
        ];

        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willReturn($sysFileData);

        $this->databaseUtilityMock
            ->method('findSysFileMetaDataById')
            ->with(5)
            ->willReturn($sysFileMetaData);

        $this->databaseUtilityMock
            ->expects(self::once())
            ->method('updateSysFile')
            ->with(5, self::anything());

        $this->databaseUtilityMock
            ->expects(self::once())
            ->method('updateSysFileMetaData')
            ->with(10, self::anything());

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        $utility = new MetaDataUtility();
        $utility->updateMetaData($this->tempDir . '/', 'test.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataSkipsWhenNoMetaDataFound(): void
    {
        // Create a real test image
        $testImagePath = $this->tempDir . '/test.jpg';
        $this->createTestImage($testImagePath);

        $sysFileData = [
            ['uid' => 5, 'identifier' => '/fileadmin/test.jpg'],
        ];

        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willReturn($sysFileData);

        $this->databaseUtilityMock
            ->method('findSysFileMetaDataById')
            ->with(5)
            ->willReturn([]);

        $this->databaseUtilityMock
            ->expects(self::once())
            ->method('updateSysFile')
            ->with(5, self::anything());

        // updateSysFileMetaData should not be called
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('updateSysFileMetaData');

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        $utility = new MetaDataUtility();
        $utility->updateMetaData($this->tempDir . '/', 'test.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataHandlesDriverExceptionGracefully(): void
    {
        // Create a mock that implements DriverException interface
        $exceptionMock = $this->createMock(DriverException::class);

        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willThrowException($exceptionMock);

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        // Should not throw exception - it's caught internally
        $utility = new MetaDataUtility();
        $utility->updateMetaData('/var/www/html/fileadmin/', 'test/image.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataHandlesInvalidUid(): void
    {
        $sysFileData = [
            ['uid' => null, 'identifier' => '/fileadmin/test.jpg'],
        ];

        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willReturn($sysFileData);

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        // Should not throw exception
        $utility = new MetaDataUtility();
        $utility->updateMetaData('/var/www/html/fileadmin/', 'test/image.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataHandlesZeroUid(): void
    {
        $sysFileData = [
            ['uid' => 0, 'identifier' => '/fileadmin/test.jpg'],
        ];

        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willReturn($sysFileData);

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        // Should not throw exception
        $utility = new MetaDataUtility();
        $utility->updateMetaData('/var/www/html/fileadmin/', 'test/image.jpg');

        self::assertTrue(true);
    }

    #[Test]
    public function updateMetaDataHandlesEmptySysFileResult(): void
    {
        $this->databaseUtilityMock
            ->method('findSysFileByIdentifier')
            ->willReturn([]);

        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);

        // Should not throw exception - empty result is handled
        $utility = new MetaDataUtility();
        $utility->updateMetaData('/var/www/html/fileadmin/', 'test/image.jpg');

        self::assertTrue(true);
    }

    /**
     * Create a simple test JPEG image
     */
    private function createTestImage(string $path): void
    {
        $image = imagecreatetruecolor(100, 100);
        if ($image !== false) {
            $white = imagecolorallocate($image, 255, 255, 255);
            if ($white !== false) {
                imagefill($image, 0, 0, $white);
            }
            imagejpeg($image, $path, 90);
            imagedestroy($image);
        }
    }
}
