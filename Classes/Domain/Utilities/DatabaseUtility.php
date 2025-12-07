<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Utilities;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseUtility
{
    /**
     * @param string $identifier
     * @throws DBALException
     * @throws Exception
     * @return bool
     */
    public function findByIdentifier(
        string $identifier,
    ): bool {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_twohtinypng_domain_model_tiny');

        if (!empty($identifier)) {
            $result = $queryBuilder
                ->select('uid')
                ->from('tx_twohtinypng_domain_model_tiny')
                ->where(
                    $queryBuilder->expr()->eq(
                        'identifier',
                        $queryBuilder->createNamedParameter($identifier),
                    ),
                    $queryBuilder->expr()->eq(
                        'deleted',
                        $queryBuilder->createNamedParameter(0, PDO::PARAM_INT),
                    ),
                )
                ->executeQuery();

            $count = $result->fetchAllAssociative();

            if (\count($count) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $identifier
     * @throws DBALException
     * @throws Exception
     * @return array
     */
    public function findSysFileByIdentifier(
        string $identifier,
    ): array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file');

        if (!empty($identifier)) {
            $result = $queryBuilder
                ->select('*')
                ->from('sys_file')
                ->where(
                    $queryBuilder->expr()->like(
                        'identifier',
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($identifier) . '%'),
                    ),
                )
                ->executeQuery();

            return $result->fetchAllAssociative();
        }

        return [];
    }

    /**
     * @param int $id
     * @throws DBALException
     * @throws Exception
     * @return array
     */
    public function findSysFileMetaDataById(
        int $id,
    ): array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_metadata');

        if (!empty($id)) {
            $result = $queryBuilder
                ->select('*')
                ->from('sys_file_metadata')
                ->where(
                    $queryBuilder->expr()->eq(
                        'file',
                        $queryBuilder->createNamedParameter(
                            $id,
                            PDO::PARAM_INT,
                        ),
                    ),
                )
                ->executeQuery();

            return $result->fetchAllAssociative();
        }

        return [];
    }

    /**
     * @param array $tinyPngArray
     * @throws DBALException
     */
    public function add(
        array $tinyPngArray,
    ): void {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_twohtinypng_domain_model_tiny');
        $queryBuilder
            ->insert('tx_twohtinypng_domain_model_tiny')
            ->values($tinyPngArray)
            ->executeStatement();
    }

    /**
     * @param int $uid
     * @param int $fileSize
     * @throws DBALException
     */
    public function updateSysFile(
        int $uid,
        int $fileSize,
    ): void {
        // sys_file
        // size: $fileSize
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file');
        $queryBuilder
            ->update('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        $uid,
                        PDO::PARAM_INT,
                    ),
                ),
            )
            ->set('size', $fileSize)
            ->executeStatement();
    }

    /**
     * @param int $uid
     * @param array $currentFileDimensions
     * @throws DBALException
     */
    public function updateSysFileMetaData(
        int $uid,
        array $currentFileDimensions,
    ): void {
        // sys_file_metadata
        // width: $currentFileDimensions[0]
        // height: $currentFileDimensions[1]
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_metadata');
        $queryBuilder
            ->update('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        $uid,
                        PDO::PARAM_INT,
                    ),
                ),
            )
            ->set('width', $currentFileDimensions[0])
            ->set('height', $currentFileDimensions[1])
            ->executeStatement();
    }
}
