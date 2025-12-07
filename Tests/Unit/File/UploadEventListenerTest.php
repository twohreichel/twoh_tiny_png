<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\File;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionClass;
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
    private MockObject&File $fileMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->tinyPngServiceMock = $this->createMock(TinyPngService::class);
        $this->fileMock = $this->createMock(File::class);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * Create a real AfterFileCommandProcessedEvent instance since it's a final class
     * The constructor requires: array $command, mixed $result, string $conflictMode
     */
    private function createEvent(array $command, mixed $result, string $conflictMode = ''): AfterFileCommandProcessedEvent
    {
        return new AfterFileCommandProcessedEvent($command, $result, $conflictMode);
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
        $event = $this->createEvent(['copy' => []], null);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeProcessesUploadCommand(): void
    {
        $this->fileMock
            ->method('getExtension')
            ->willReturn('jpg');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.jpg');

        $event = $this->createEvent(['upload' => []], [$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    #[DataProvider('supportedImageExtensionDataProvider')]
    public function invokeProcessesSupportedImageExtensions(string $extension): void
    {
        $this->fileMock
            ->method('getExtension')
            ->willReturn($extension);

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.' . $extension);

        $event = $this->createEvent(['upload' => []], [$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

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
        $this->fileMock
            ->method('getExtension')
            ->willReturn($extension);

        $event = $this->createEvent(['upload' => []], [$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

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
        $event = $this->createEvent(['upload' => []], ['not a file object']);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeHandlesEmptyResult(): void
    {
        $event = $this->createEvent(['upload' => []], []);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeLogsExceptionOnError(): void
    {
        $exception = new Exception('Compression failed');

        $this->fileMock
            ->method('getExtension')
            ->willReturn('jpg');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/user_upload/image.jpg');

        $event = $this->createEvent(['upload' => []], [$this->fileMock]);

        $this->tinyPngServiceMock
            ->method('imageCompression')
            ->willThrowException($exception);

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with('Compression failed');

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    public function invokeUsesCorrectPath(): void
    {
        $this->fileMock
            ->method('getExtension')
            ->willReturn('png');

        $this->fileMock
            ->method('getIdentifier')
            ->willReturn('/gallery/2024/photo.png');

        $event = $this->createEvent(['upload' => []], [$this->fileMock]);

        $this->tinyPngServiceMock
            ->expects(self::once())
            ->method('imageCompression')
            ->with(
                self::stringContains('fileadmin'),
                '/gallery/2024/photo.png',
            );

        GeneralUtility::addInstance(TinyPngService::class, $this->tinyPngServiceMock);

        $listener = new UploadEventListener($this->loggerMock);
        $listener($event);

        self::assertTrue(true);
    }

    #[Test]
    public function listenerRequiresLoggerInConstructor(): void
    {
        $reflection = new ReflectionClass(UploadEventListener::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());
        self::assertSame('logger', $constructor->getParameters()[0]->getName());
    }

    #[Test]
    public function listenerIsFinal(): void
    {
        $reflection = new ReflectionClass(UploadEventListener::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function invokeIsCallable(): void
    {
        $listener = new UploadEventListener($this->loggerMock);

        self::assertIsCallable($listener);
    }

    #[Test]
    public function invokeHandlesNullResultGracefully(): void
    {
        $event = $this->createEvent(['upload' => []], null);

        $this->tinyPngServiceMock
            ->expects(self::never())
            ->method('imageCompression');

        $listener = new UploadEventListener($this->loggerMock);

        // Should not throw an exception
        $listener($event);

        self::assertTrue(true);
    }
}
