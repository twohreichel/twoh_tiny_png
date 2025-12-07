<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TWOH\TwohTinyPng\Command\BulkMetaDataCommand;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(BulkMetaDataCommand::class)]
final class BulkMetaDataCommandTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private MockObject&MetaDataUtility $metaDataUtilityMock;
    private MockObject&InputInterface $inputMock;
    private MockObject&OutputInterface $outputMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metaDataUtilityMock = $this->createMock(MetaDataUtility::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function bulkMetaDataCommandCanBeInstantiated(): void
    {
        $command = new BulkMetaDataCommand();

        self::assertInstanceOf(BulkMetaDataCommand::class, $command);
    }

    #[Test]
    public function commandExtendsSymfonyCommand(): void
    {
        $command = new BulkMetaDataCommand();

        self::assertInstanceOf(Command::class, $command);
    }

    #[Test]
    public function executeReturnsMetaDataUtilityResult(): void
    {
        $this->metaDataUtilityMock
            ->expects(self::once())
            ->method('bulkMetaData')
            ->willReturn(Command::SUCCESS);

        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $command = new BulkMetaDataCommand();

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertSame(Command::SUCCESS, $result);
    }

    #[Test]
    public function executeReturnsFailureWhenNoFilesFound(): void
    {
        $this->metaDataUtilityMock
            ->expects(self::once())
            ->method('bulkMetaData')
            ->willReturn(Command::FAILURE);

        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $command = new BulkMetaDataCommand();

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertSame(Command::FAILURE, $result);
    }

    #[Test]
    public function configureSetHelpText(): void
    {
        $command = new BulkMetaDataCommand();

        $help = $command->getHelp();

        self::assertStringContainsString('meta', strtolower($help));
    }

    #[Test]
    public function commandNameCanBeSet(): void
    {
        $command = new BulkMetaDataCommand('tinypng:metadata');

        self::assertSame('tinypng:metadata', $command->getName());
    }

    #[Test]
    public function commandReturnsIntFromExecute(): void
    {
        $this->metaDataUtilityMock
            ->method('bulkMetaData')
            ->willReturn(Command::SUCCESS);

        GeneralUtility::addInstance(MetaDataUtility::class, $this->metaDataUtilityMock);

        $command = new BulkMetaDataCommand();

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $this->inputMock, $this->outputMock);

        self::assertIsInt($result);
    }
}
