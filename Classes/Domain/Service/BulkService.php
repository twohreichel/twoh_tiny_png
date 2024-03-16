<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Service;

use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BulkService
{
    /**
     * @return int
     * @throws Exception
     */
    public function bulkImages(): int
    {
        $path = Environment::getPublicPath() . '/' . 'fileadmin/';
        $tinyPngService = GeneralUtility::makeInstance(TinyPngService::class);

        $files = $this->scanAllDir($path);

        if (!empty($files)) {
            foreach ($files as $img) {
                if (!empty($img)) {
                    $tinyPngService->imageCompression($path, $img);
                }
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $dir
     * @return array
     */
    public function scanAllDir(
        string $dir
    ): array
    {
        $result = [];
        foreach (scandir($dir) as $filename) {
            if ($filename[0] === '.') {
                continue;
            }
            if ($filename !== '_processed_' && $filename !== '_temp_') {
                $filePath = $dir . '/' . $filename;
                if (is_dir($filePath)) {
                    foreach ($this->scanAllDir($filePath) as $childFilename) {
                        $result[] = $filename . '/' . $childFilename;
                    }
                } else {
                    $imageFileType = pathinfo($filename, PATHINFO_EXTENSION);
                    if ($imageFileType === "jpg" || $imageFileType === "png" || $imageFileType === "jpeg") {
                        $result[] = $filename;
                    }
                }
            }
        }
        return $result;
    }
}
