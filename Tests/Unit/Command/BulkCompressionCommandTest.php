<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TWOH\TwohTinyPng\Command\BulkCompressionCommand;
use TWOH\TwohTinyPng\Domain\Service\BulkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(BulkCompressionCommand::class)]
final class BulkCompressionCommandTest extends TestCase
{
    private MockObject&BulkService $bulkServiceMock;
    private MockObject&InputInterface $inputMock;
    private MockObject&OutputInterface $outputMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bulkServiceMock = $this->createMock(BulkService::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function bulkCompressionCommandCanBeInstantiated(): void
    {
        $command = new BulkCompressionCommand();

        self::assertInstanceOf(BulkCompressionCommand::class, $command);
    }

    #[Test]
    public function commandExtendsSymfonyCommand(): void
    {
        $command = new BulkCompressionCommand();

        self::assertInstanceOf(Command::class, $command);
    }

    #[Test]
    public function executeReturnsBulkServiceResult(): void
    {
        $this->bulkServiceMock
            ->expects(self::once())
            ->method('bulkImages')
            ->willReturn(Command::SUCCESS);

        GeneralUtility::addInstance(BulkService::class, $this->bulkServiceMock);

        $command = new BulkCompressionCommand();

        // Use reflection to call protected execute method
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertSame(Command::SUCCESS, $result);
    }

    #[Test]
    public function executeReturnsFailureWhenBulkServiceFails(): void
    {
        $this->bulkServiceMock
            ->expects(self::once())
            ->method('bulkImages')
            ->willReturn(Command::FAILURE);

        GeneralUtility::addInstance(BulkService::class, $this->bulkServiceMock);

        $command = new BulkCompressionCommand();

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertSame(Command::FAILURE, $result);
    }

    #[Test]
    public function configureSetHelpText(): void
    {
        $command = new BulkCompressionCommand();

        // Configure is called in constructor, so we can check the help
        $help = $command->getHelp();

        self::assertStringContainsString('compressed', $help);
        self::assertStringContainsString('fileadmin', $help);
    }

    #[Test]
    public function commandNameCanBeSet(): void
    {
        $command = new BulkCompressionCommand('tinypng:compress');

        self::assertSame('tinypng:compress', $command->getName());
    }

    #[Test]
    public function commandReturnsIntFromExecute(): void
    {
        $this->bulkServiceMock
            ->method('bulkImages')
            ->willReturn(Command::SUCCESS);

        GeneralUtility::addInstance(BulkService::class, $this->bulkServiceMock);

        $command = new BulkCompressionCommand();

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertIsInt($result);
    }
}
