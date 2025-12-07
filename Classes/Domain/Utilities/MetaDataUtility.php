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

        if (\count($files) > 0) {
            foreach ($files as $file) {
                $this->updateMetaData(
                    $path,
                    $file,
                );
            }

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @param string $path
     * @param string $file
     */
    public function updateMetaData(
        string $path,
        string $file,
    ): void {
        $databaseUtility = GeneralUtility::makeInstance(DatabaseUtility::class);

        try {
            $sysFile = $databaseUtility->findSysFileByIdentifier($file);

            if (!isset($sysFile[0]['uid']) || empty($sysFile[0]['uid'])) {
                return;
            }

            $uid = (int)$sysFile[0]['uid'];
            $currentFileDimensions = getimagesize($path . $file);
            $fileSize = filesize($path . $file);

            if ($fileSize) {
                $databaseUtility->updateSysFile($uid, $fileSize);
            }

            $sysFileMetaData = $databaseUtility->findSysFileMetaDataById($uid);

            if (
                isset($sysFileMetaData[0]['uid'])
                && $sysFileMetaData[0]['uid'] > 0
                && isset($currentFileDimensions[0], $currentFileDimensions[1])
            ) {
                $databaseUtility->updateSysFileMetaData(
                    (int)$sysFileMetaData[0]['uid'],
                    $currentFileDimensions,
                );
            }
        } catch (DBALException|Exception $e) {
            // Silently catch database exceptions
        }
    }
}
