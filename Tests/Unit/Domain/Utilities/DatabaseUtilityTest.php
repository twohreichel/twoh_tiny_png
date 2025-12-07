<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Utilities;

use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(DatabaseUtility::class)]
final class DatabaseUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private MockObject&ConnectionPool $connectionPoolMock;
    private MockObject&QueryBuilder $queryBuilderMock;
    private MockObject&ExpressionBuilder $expressionBuilderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionPoolMock = $this->createMock(ConnectionPool::class);
        $this->queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'select',
                'from',
                'where',
                'executeQuery',
                'executeStatement',
                'insert',
                'values',
                'update',
                'set',
                'expr',
                'createNamedParameter',
                'escapeLikeWildcards',
            ])
            ->getMock();
        $this->expressionBuilderMock = $this->createMock(ExpressionBuilder::class);

        $this->queryBuilderMock
            ->method('expr')
            ->willReturn($this->expressionBuilderMock);

        $this->queryBuilderMock
            ->method('createNamedParameter')
            ->willReturnCallback(function ($value, $type = null) {
                return ':dcValue1';
            });
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
    public function findByIdentifierReturnsTrueWhenNoRecordFound(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn([]);

        $this->expressionBuilderMock->method('eq')->willReturn('identifier = :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findByIdentifier('test/image.jpg');

        self::assertTrue($result);
    }

    #[Test]
    public function findByIdentifierReturnsFalseWhenRecordExists(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn([['uid' => 1]]);

        $this->expressionBuilderMock->method('eq')->willReturn('identifier = :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

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

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn($expectedData);

        $this->expressionBuilderMock->method('like')->willReturn('identifier LIKE :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('escapeLikeWildcards')->willReturn('test/image.jpg');
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

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

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn($expectedData);

        $this->expressionBuilderMock->method('eq')->willReturn('file = :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

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

        $this->queryBuilderMock->method('insert')->willReturnSelf();
        $this->queryBuilderMock->method('values')->willReturnSelf();
        $this->queryBuilderMock->expects(self::once())->method('executeStatement')->willReturn(1);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->add($tinyPngArray);

        self::assertTrue(true);
    }

    #[Test]
    public function updateSysFileUpdatesFileSize(): void
    {
        $this->expressionBuilderMock->method('eq')->willReturn('uid = :dcValue1');
        $this->queryBuilderMock->method('update')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('set')->willReturnSelf();
        $this->queryBuilderMock->expects(self::once())->method('executeStatement')->willReturn(1);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->updateSysFile(5, 123456);

        self::assertTrue(true);
    }

    #[Test]
    public function updateSysFileMetaDataUpdatesDimensions(): void
    {
        $this->expressionBuilderMock->method('eq')->willReturn('uid = :dcValue1');
        $this->queryBuilderMock->method('update')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('set')->willReturnSelf();
        $this->queryBuilderMock->expects(self::once())->method('executeStatement')->willReturn(1);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $utility->updateSysFileMetaData(10, [1920, 1080]);

        self::assertTrue(true);
    }

    #[Test]
    #[DataProvider('validIdentifierDataProvider')]
    public function findByIdentifierHandlesVariousValidIdentifiers(string $identifier): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn([]);

        $this->expressionBuilderMock->method('eq')->willReturn('identifier = :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findByIdentifier($identifier);
        self::assertTrue($result);
    }

    public static function validIdentifierDataProvider(): array
    {
        return [
            'simple path' => ['image.jpg'],
            'nested path' => ['folder/subfolder/image.png'],
            'path with special chars' => ['folder/bild_uebersicht-2024.jpg'],
        ];
    }

    #[Test]
    public function findByIdentifierHandlesMultipleRecords(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')->willReturn([['uid' => 1], ['uid' => 2]]);

        $this->expressionBuilderMock->method('eq')->willReturn('identifier = :dcValue1');
        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('from')->willReturnSelf();
        $this->queryBuilderMock->method('where')->willReturnSelf();
        $this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);

        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);

        $utility = new DatabaseUtility();
        $result = $utility->findByIdentifier('test/image.jpg');

        self::assertFalse($result);
    }
}
