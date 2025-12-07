<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Utilities;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(DatabaseUtility::class)]
final class DatabaseUtilityTest extends TestCase
{
    private MockObject&ConnectionPool $connectionPoolMock;
    private MockObject&QueryBuilder $queryBuilderMock;
    private MockObject&ExpressionBuilder $expressionBuilderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionPoolMock = $this->createMock(ConnectionPool::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->expressionBuilderMock = $this->createMock(ExpressionBuilder::class);

        $this->queryBuilderMock
            ->method('expr')
            ->willReturn($this->expressionBuilderMock);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function databaseUtilityCanBeInstantiated(): void
    {
        $utility = new DatabaseUtility();

        self::assertInstanceOf(DatabaseUtility::class, $utility);
    }

    #[Test]
    public function findByIdentifierReturnsFalseForEmptyIdentifier(): void
    {
        $utility = new DatabaseUtility();

        $result = $utility->findByIdentifier('');

        self::assertFalse($result);
    }

    #[Test]
    public function findSysFileByIdentifierReturnsEmptyArrayForEmptyIdentifier(): void
    {
        $utility = new DatabaseUtility();

        $result = $utility->findSysFileByIdentifier('');

        self::assertSame([], $result);
    }

    #[Test]
    public function findSysFileMetaDataByIdReturnsEmptyArrayForZeroId(): void
    {
        $utility = new DatabaseUtility();

        $result = $utility->findSysFileMetaDataById(0);

        self::assertSame([], $result);
    }

    #[Test]
    public function findByIdentifierReturnsTrueWhenNoRecordFound(): void
    {
        $resultMock = $this->createMock(\Doctrine\DBAL\Result::class);
        $resultMock
            ->method('fetchAllAssociative')
            ->willReturn([]);

        $this->expressionBuilderMock
            ->method('eq')
            ->willReturn('identifier = :dcValue1');

        $this->queryBuilderMock
            ->method('select')
            ->with('uid')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('from')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('executeQuery')
            ->willReturn($resultMock);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findByIdentifier('test/image.jpg');

        self::assertTrue($result);
    }

    #[Test]
    public function findByIdentifierReturnsFalseWhenRecordExists(): void
    {
        $resultMock = $this->createMock(\Doctrine\DBAL\Result::class);
        $resultMock
            ->method('fetchAllAssociative')
            ->willReturn([['uid' => 1]]);

        $this->expressionBuilderMock
            ->method('eq')
            ->willReturn('identifier = :dcValue1');

        $this->queryBuilderMock
            ->method('select')
            ->with('uid')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('from')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('executeQuery')
            ->willReturn($resultMock);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findByIdentifier('test/image.jpg');

        self::assertFalse($result);
    }

    #[Test]
    public function findSysFileByIdentifierReturnsResultsWhenFound(): void
    {
        $expectedData = [
            ['uid' => 1, 'identifier' => '/fileadmin/test/image.jpg', 'name' => 'image.jpg'],
        ];

        $resultMock = $this->createMock(\Doctrine\DBAL\Result::class);
        $resultMock
            ->method('fetchAllAssociative')
            ->willReturn($expectedData);

        $this->expressionBuilderMock
            ->method('like')
            ->willReturn('identifier LIKE :dcValue1');

        $this->queryBuilderMock
            ->method('select')
            ->with('*')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('from')
            ->with('sys_file')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('escapeLikeWildcards')
            ->willReturn('test/image.jpg');

        $this->queryBuilderMock
            ->method('executeQuery')
            ->willReturn($resultMock);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('sys_file')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findSysFileByIdentifier('test/image.jpg');

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function findSysFileMetaDataByIdReturnsResultsWhenFound(): void
    {
        $expectedData = [
            ['uid' => 1, 'file' => 5, 'width' => 1920, 'height' => 1080],
        ];

        $resultMock = $this->createMock(\Doctrine\DBAL\Result::class);
        $resultMock
            ->method('fetchAllAssociative')
            ->willReturn($expectedData);

        $this->expressionBuilderMock
            ->method('eq')
            ->willReturn('file = :dcValue1');

        $this->queryBuilderMock
            ->method('select')
            ->with('*')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('from')
            ->with('sys_file_metadata')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('executeQuery')
            ->willReturn($resultMock);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('sys_file_metadata')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findSysFileMetaDataById(5);

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function addInsertsRecordIntoDatabase(): void
    {
        $tinyPngArray = [
            'identifier' => 'test/image.jpg',
            'dimension' => 2560,
            'pid' => 1,
        ];

        $this->queryBuilderMock
            ->method('insert')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('values')
            ->with($tinyPngArray)
            ->willReturnSelf();

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('tx_twohtinypng_domain_model_tiny')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->add($tinyPngArray);

        // If no exception, test passes
        self::assertTrue(true);
    }

    #[Test]
    public function updateSysFileUpdatesFileSize(): void
    {
        $uid = 5;
        $fileSize = 123456;

        $this->expressionBuilderMock
            ->method('eq')
            ->willReturn('uid = :dcValue1');

        $this->queryBuilderMock
            ->method('update')
            ->with('sys_file')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('set')
            ->with('size', $fileSize)
            ->willReturnSelf();

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('sys_file')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->updateSysFile($uid, $fileSize);

        self::assertTrue(true);
    }

    #[Test]
    public function updateSysFileMetaDataUpdatesDimensions(): void
    {
        $uid = 10;
        $currentFileDimensions = [1920, 1080];

        $this->expressionBuilderMock
            ->method('eq')
            ->willReturn('uid = :dcValue1');

        $this->queryBuilderMock
            ->method('update')
            ->with('sys_file_metadata')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('where')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturn(':dcValue1');

        $this->queryBuilderMock
            ->method('set')
            ->willReturnSelf();

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $this->connectionPoolMock
            ->method('getQueryBuilderForTable')
            ->with('sys_file_metadata')
            ->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->updateSysFileMetaData($uid, $currentFileDimensions);

        self::assertTrue(true);
    }

    #[Test]
    #[DataProvider('identifierDataProvider')]
    public function findByIdentifierHandlesVariousIdentifiers(string $identifier, bool $isEmpty): void
    {
        if ($isEmpty) {
            $utility = new DatabaseUtility();
            $result = $utility->findByIdentifier($identifier);
            self::assertFalse($result);
        } else {
            $resultMock = $this->createMock(\Doctrine\DBAL\Result::class);
            $resultMock
                ->method('fetchAllAssociative')
                ->willReturn([]);

            $this->expressionBuilderMock
                ->method('eq')
                ->willReturn('identifier = :dcValue1');

            $this->queryBuilderMock
                ->method('select')
                ->willReturnSelf();

            $this->queryBuilderMock
                ->method('from')
                ->willReturnSelf();

            $this->queryBuilderMock
                ->method('where')
                ->willReturnSelf();

            $this->queryBuilderMock
                ->method('createNamedParameter')
                ->willReturn(':dcValue1');

            $this->queryBuilderMock
                ->method('executeQuery')
                ->willReturn($resultMock);

            $this->connectionPoolMock
                ->method('getQueryBuilderForTable')
                ->willReturn($this->queryBuilderMock);

            GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

            $utility = new DatabaseUtility();
            $result = $utility->findByIdentifier($identifier);
            self::assertTrue($result);
        }
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function identifierDataProvider(): array
    {
        return [
            'empty identifier' => ['', true],
            'simple path' => ['image.jpg', false],
            'nested path' => ['folder/subfolder/image.png', false],
            'path with special chars' => ['folder/bild_übersicht-2024.jpg', false],
        ];
    }
}
