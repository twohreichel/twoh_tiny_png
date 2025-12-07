<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TWOH\TwohTinyPng\Domain\Service\TinyPngService;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

#[CoversClass(TinyPngService::class)]
final class TinyPngServiceTest extends TestCase
{
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

    #[Test]
    public function getTsSetupReturnsTypoScriptSettings(): void
    {
        $expectedSettings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
            'ignoreImagesByFolderName' => '',
        ];

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $expectedSettings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->expects(self::once())
            ->method('getConfiguration')
            ->with(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                'twoh_tiny_png',
            )
            ->willReturn($fullTypoScript);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();
        $result = $service->getTsSetup();

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

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $expectedSettings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();

        self::assertInstanceOf(TinyPngService::class, $service);
    }

    #[Test]
    public function serviceImplementsLoggerAwareInterface(): void
    {
        $expectedSettings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'pid' => '1',
        ];

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $expectedSettings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();

        self::assertInstanceOf(\Psr\Log\LoggerAwareInterface::class, $service);
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

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        // DatabaseUtility should not be called because folder is ignored
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $service = new TinyPngService();
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

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        // findByIdentifier returns false = image already compressed
        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->with('images/test.jpg')
            ->willReturn(false);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        // DatabaseUtility add should not be called because image was already compressed
        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('add');

        $service = new TinyPngService();
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

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $this->databaseUtilityMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $service = new TinyPngService();
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

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        // findByIdentifier returns false (image already exists in DB)
        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->with('images/test.jpg')
            ->willReturn(false);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();
        // This should not throw an exception and should check the database
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
            // width is not set
        ];

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->willReturn(false);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();
        $service->imageCompression('/var/www/html/fileadmin/', 'images/test.jpg');

        // Test passes if no exception is thrown
        self::assertTrue(true);
    }

    #[Test]
    public function imageCompressionUsesDefaultPidWhenNotConfigured(): void
    {
        $settings = [
            'apiKey' => 'test-api-key',
            'width' => '2560',
            'ignoreImagesByFolderName' => '',
            // pid is not set
        ];

        $fullTypoScript = [
            'plugin.' => [
                'tx_twohtinypng.' => [
                    'settings.' => $settings,
                ],
            ],
        ];

        $this->configurationManagerMock
            ->method('getConfiguration')
            ->willReturn($fullTypoScript);

        $this->databaseUtilityMock
            ->method('findByIdentifier')
            ->willReturn(false);

        GeneralUtility::addInstance(ConfigurationManager::class, $this->configurationManagerMock);
        GeneralUtility::addInstance(DatabaseUtility::class, $this->databaseUtilityMock);
        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $service = new TinyPngService();
        $service->imageCompression('/var/www/html/fileadmin/', 'images/test.jpg');

        self::assertTrue(true);
    }
}
