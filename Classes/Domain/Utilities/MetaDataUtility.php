<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Utilities;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Command\Command;
use TWOH\TwohTinyPng\Domain\Service\BulkService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetaDataUtility
{
    /**
     * @return int
     */
    public function bulkMetaData(): int
    {

        $bulkService = GeneralUtility::makeInstance(BulkService::class);

        $path = Environment::getPublicPath() . '/' . 'fileadmin/';
        $files = $bulkService->scanAllDir($path);

        if (count($files) > 0) {
            foreach ($files as $file) {
                $this->updateMetaData(
                    $path,
                    $file
                );
            }

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @param string $path
     * @param string $file
     * @return void
     */
    public function updateMetaData(
        string $path,
        string $file
    ): void
    {
        $databaseUtility = GeneralUtility::makeInstance(DatabaseUtility::class);

        try {
            $sysFile = $databaseUtility->findSysFileByIdentifier($file);

            if ($sysFile[0]['uid']) {
                $currentFileDimensions = getimagesize(
                    $path . $file
                );
                $fileSize = filesize(
                    $path . $file
                );

                $sysFileMetaData = $databaseUtility->findSysFileMetaDataById($sysFile[0]['uid']);

                if ($fileSize) {
                    $databaseUtility->updateSysFile(
                        $sysFile[0]['uid'],
                        $fileSize
                    );
                }

                if ($sysFileMetaData[0]['uid'] > 0 && isset($currentFileDimensions[0]) && isset($currentFileDimensions[1])) {
                    $databaseUtility->updateSysFileMetaData(
                        $sysFileMetaData[0]['uid'],
                        $currentFileDimensions
                    );
                }
            }
        } catch (DBALException|Exception $e) {
        }
    }
}