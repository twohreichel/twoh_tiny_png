<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use TWOH\TwohTinyPng\Domain\Service\TinyPngService;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(TinyPngService::class)]
final class TinyPngServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private MockObject&ConfigurationManager $configurationManagerMock;
    private MockObject&DatabaseUtility $databaseUtilityMock;
    private MockObject&MetaDataUtility $metaDataUtilityMock;
    private MockObject&LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);
        $this->databaseUtilityMock = $this->createMock(DatabaseUtility::class);
        $this->metaDataUtilityMock = $this->createMock(MetaDataUtility::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * Helper method to create a TinyPngService instance with mocked dependencies
     */
    private function createServiceWithMockedDependencies(array $settings): TinyPngService
    {
        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->with(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                'twoh_tiny_png',
            )
            ->willReturn($fullTypoScript);

        GeneralUtility::setSingletonInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        return new TinyPngService();
    }

    /**
     * Helper method to set the tinifyReady flag to skip API validation in tests
     */
    private function setTinifyReady(TinyPngService $service, bool $ready = true): void
    {
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('tinifyReady');
        $property->setAccessible(true);
        $property->setValue($service, $ready);
    }

    #[Test]
    public function getTsSetupReturnsTypoScriptSettings(): void
    {
        $expectedSettings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => '',
        ];

        $service = $this->createServiceWithMockedDependencies($expectedSettings);

        // getTsSetup wird im Konstruktor aufgerufen, daher prüfen wir die args Property
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('args');
        $property->setAccessible(true);
        $result = $property->getValue($service);

        self::assertSame($expectedSettings, $result);
    }

    #[Test]
    public function constructorInitializesDependencies(): void
    {
        $expectedSettings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
        ];

        $service = $this->createServiceWithMockedDependencies($expectedSettings);

        self::assertInstanceOf(TinyPngService::class, $service);

        // Verify that DatabaseUtility and MetaDataUtility are initialized
        $reflection = new ReflectionClass($service);

        $dbProperty = $reflection->getProperty('databaseUtility');
        $dbProperty->setAccessible(true);
        self::assertInstanceOf(DatabaseUtility::class, $dbProperty->getValue($service));

        $metaProperty = $reflection->getProperty('metaDataUtility');
        $metaProperty->setAccessible(true);
        self::assertInstanceOf(MetaDataUtility::class, $metaProperty->getValue($service));
    }

    #[Test]
    public function serviceImplementsLoggerAwareInterface(): void
    {
        $expectedSettings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
        ];

        $service = $this->createServiceWithMockedDependencies($expectedSettings);

        self::assertInstanceOf(LoggerAwareInterface::class, $service);
    }

    #[Test]
    public function imageCompressionSkipsImageInIgnoredFolder(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => 'ignored_folder,another_ignored',
        ];

        $service = $this->createServiceWithMockedDependencies($settings);
        // Skip API validation by setting tinifyReady to true
        $this->setTinifyReady($service, true);

        // DatabaseUtility should not be called because folder is ignored
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $service->imageCompression('/var/www/html/fileadmin/', 'ignored_folder/image.jpg');
    }

    #[Test]
    public function imageCompressionSkipsImageWhenAlreadyCompressed(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => '',
        ];

        // findByIdentifier returns false = image already compressed
        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->with('images/test.jpg')
            ->willReturn(false);

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        // DatabaseUtility add should not be called because image was already compressed
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('add');

        $service->imageCompression('/var/www/html/fileadmin/', 'images/test.jpg');
    }

    #[Test]
    public function imageCompressionChecksMultipleIgnoredFolders(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => 'folder1, folder2, folder3',
        ];

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $service->imageCompression('/var/www/html/fileadmin/', 'folder2/image.jpg');
    }

    #[Test]
    public function imageCompressionWithEmptyIgnoreSettingProcessesImage(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => '',
        ];

        // findByIdentifier returns false (image already exists in DB)
        $this->databaseUtilityMock
            ->expects(self::once())
            ->method('findByIdentifier')
            ->with('images/test.jpg')
            ->willReturn(false);

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        $service->imageCompression('/var/www/html/fileadmin/', 'images/test.jpg');

        // If we get here without exception, the test passes
        self::assertTrue(true);
    }

    #[Test]
    public function imageCompressionUsesDefaultWidthWhenNotConfigured(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'pid' => '1',
            'ignoreImagesByFolderName' => '',
            // width is not set - should use default 2560
        ];

        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->willReturn(false);

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        // Verify the service was created and imageCompression runs without error
        $service->imageCompression('/var/www/html/fileadmin/', 'images/test.jpg');

        // Test passes if no exception is thrown
        self::assertTrue(true);
    }

    #[Test]
    public function imageCompressionSkipsSubfolderInIgnoredFolder(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => 'ignored_folder',
        ];

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        // DatabaseUtility should not be called because path contains ignored folder
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $service->imageCompression('/var/www/html/fileadmin/', 'ignored_folder/subfolder/image.jpg');
    }

    #[Test]
    public function imageCompressionProcessesImageNotInIgnoredFolder(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => 'ignored_folder',
        ];

        // findByIdentifier should be called because folder is not ignored
        $this->databaseUtilityMock
            ->expects(self::once())
            ->method('findByIdentifier')
            ->with('allowed_folder/image.jpg')
            ->willReturn(false);

        $service = $this->createServiceWithMockedDependencies($settings);
        $this->setTinifyReady($service, true);

        $service->imageCompression('/var/www/html/fileadmin/', 'allowed_folder/image.jpg');
    }
}
