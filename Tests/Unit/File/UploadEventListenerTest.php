<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TWOH\TwohTinyPng\Domain\Service\TinyPngService;
use TWOH\TwohTinyPng\File\UploadEventListener;
use TYPO3\CMS\Core\Resource\Event\AfterFileCommandProcessedEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UploadEventListener::class)]
final class UploadEventListenerTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private MockObject&LoggerInterface $loggerMock;
    private MockObject&TinyPngService $tinyPngServiceMock;
    private MockObject&AfterFileCommandProcessedEvent $eventMock;
    private MockObject&File $fileMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->tinyPngServiceMock = $this->createMock(TinyPngService::class);
        $this->eventMock = $this->createMock(AfterFileCommandProcessedEvent::class);
        $this->fileMock = $this->createMock(File::class);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function uploadEventListenerCanBeInstantiated(): void
    {
        $listener = new UploadEventListener($this->loggerMock);

        self::assertInstanceOf(UploadEventListener::class, $listener);
    }

    #[Test]
    public function invokeSkipsNonUploadCommands(): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['copy' => []]);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeProcessesUploadCommand(): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->fileMock
            ->method('getExtension')
            ->willReturn('jpg');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.jpg');

        $this->eventMock
            ->method('getResult')
            ->willReturn([$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    #[DataProvider('supportedImageExtensionDataProvider')]
    public function invokeProcessesSupportedImageExtensions(string $extension): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->fileMock
            ->method('getExtension')
            ->willReturn($extension);

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.' . $extension);

        $this->eventMock
            ->method('getResult')
            ->willReturn([$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function supportedImageExtensionDataProvider(): array
    {
        return [
            'jpg extension' => ['jpg'],
            'jpeg extension' => ['jpeg'],
            'png extension' => ['png'],
        ];
    }

    #[Test]
    #[DataProvider('unsupportedImageExtensionDataProvider')]
    public function invokeSkipsUnsupportedImageExtensions(string $extension): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->fileMock
            ->method('getExtension')
            ->willReturn($extension);

        $this->eventMock
            ->method('getResult')
            ->willReturn([$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unsupportedImageExtensionDataProvider(): array
    {
        return [
            'gif extension' => ['gif'],
            'webp extension' => ['webp'],
            'svg extension' => ['svg'],
            'pdf extension' => ['pdf'],
            'doc extension' => ['doc'],
            'bmp extension' => ['bmp'],
            'tiff extension' => ['tiff'],
        ];
    }

    #[Test]
    public function invokeHandlesNonFileResult(): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->eventMock
            ->method('getResult')
            ->willReturn(['not a file object']);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeHandlesEmptyResult(): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->eventMock
            ->method('getResult')
            ->willReturn([]);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeLogsExceptionOnError(): void
    {
        $exception = new \Exception('Compression failed');

        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->fileMock
            ->method('getExtension')
            ->willReturn('jpg');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.jpg');

        $this->eventMock
            ->method('getResult')
            ->willReturn([$this->fileMock]);

        $this->tinyPngServiceMock
            ->method('imageCompression')
            ->willThrowException($exception);

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with('Compression failed');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeUsesCorrectPath(): void
    {
        $this->eventMock
            ->method('getCommand')
            ->willReturn(['upload' => []]);

        $this->fileMock
            ->method('getExtension')
            ->willReturn('png');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/gallery/2024/photo.png');

        $this->eventMock
            ->method('getResult')
            ->willReturn([$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression')
            ->with(
                self::stringContains('fileadmin'),
                '/gallery/2024/photo.png',
            );

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($this->eventMock);

        self::assertTrue(true);
    }

    #[Test]
    public function listenerRequiresLoggerInConstructor(): void
    {
        $reflection = new \ReflectionClass(UploadEventListener::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());
        self::assertSame('logger', $constructor->getParameters()[0]->getName());
    }

    #[Test]
    public function listenerIsFinal(): void
    {
        $reflection = new \ReflectionClass(UploadEventListener::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function invokeIsCallable(): void
    {
        $listener = new UploadEventListener($this->loggerMock);

        self::assertTrue(is_callable($listener));
    }
}
