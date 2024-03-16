<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Utilities;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseUtility
{
    /**
     * @param string $identifier
     * @return bool
     * @throws DBALException
     * @throws Exception
     */
    public function findByIdentifier(
        string $identifier
    ): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_twohtinypng_domain_model_tiny');
        
        if (!empty($identifier)) {
            $result = $queryBuilder
                ->select('uid')
                ->from('tx_twohtinypng_domain_model_tiny')
                ->where(
                    $queryBuilder->expr()->eq(
                        'identifier',
                        $queryBuilder->createNamedParameter($identifier)
                    ),
                    $queryBuilder->expr()->eq(
                        'deleted',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    )
                )
                ->execute();

            $count = $result->fetchAllAssociative();

            if (count($count) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $identifier
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function findSysFileByIdentifier(
        string $identifier
    ): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file');

        if (!empty($identifier)) {
            $result = $queryBuilder
                ->select('*')
                ->from('sys_file')
                ->where(
                    $queryBuilder->expr()->like(
                        'identifier',
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($identifier) . '%')
                    )
                )
                ->execute();

            return $result->fetchAllAssociative();
        }

        return [];
    }

    /**
     * @param int $id
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function findSysFileMetaDataById(
        int $id
    ): array
    {
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
                            \PDO::PARAM_INT
                        )
                    )
                )
                ->execute();

            return $result->fetchAllAssociative();
        }

        return [];
    }

    /**
     * @param array $tinyPngArray
     * @return void
     * @throws DBALException
     */
    public function add(
        array $tinyPngArray
    ): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_twohtinypng_domain_model_tiny');
        $queryBuilder
            ->insert('tx_twohtinypng_domain_model_tiny')
            ->values($tinyPngArray)
            ->execute();
    }

    /**
     * @param int $uid
     * @param int $fileSize
     * @return void
     * @throws DBALException
     */
    public function updateSysFile(
        int $uid,
        int $fileSize
    ): void
    {
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
                        \PDO::PARAM_INT
                    )
                )
            )
            ->set('size', $fileSize)
            ->execute();
    }

    /**
     * @param int $uid
     * @param array $currentFileDimensions
     * @return void
     * @throws DBALException
     */
    public function updateSysFileMetaData(
        int $uid,
        array $currentFileDimensions
    ): void
    {
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
                        \PDO::PARAM_INT
                    )
                )
            )
            ->set('width', $currentFileDimensions[0])
            ->set('height', $currentFileDimensions[1])
            ->execute();
    }
}