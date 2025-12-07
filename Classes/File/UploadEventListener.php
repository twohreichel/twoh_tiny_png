<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\File;

use Doctrine\DBAL\Driver\Exception;
use Psr\Log\LoggerInterface;
use TWOH\TwohTinyPng\Domain\Service\TinyPngService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Event\AfterFileCommandProcessedEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class UploadEventListener
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AfterFileCommandProcessedEvent $event
     * @throws Exception
     */
    public function __invoke(AfterFileCommandProcessedEvent $event): void
    {
        if (!\array_key_exists('upload', $event->getCommand())) {
            return;
        }

        $result = $event->getResult();
        if (!\is_array($result) || !isset($result[0])) {
            return;
        }

        $fileObject = $result[0];

        if (!$fileObject instanceof File) {
            return;
        }

        $extension = $fileObject->getExtension();
        if ($extension !== 'jpg' && $extension !== 'jpeg' && $extension !== 'png') {
            return;
        }

        try {
            $tinyPngService = GeneralUtility::makeInstance(TinyPngService::class);
            $tinyPngService->imageCompression(
                Environment::getPublicPath() . '/' . 'fileadmin',
                $fileObject->getIdentifier(),
            );
        } catch (\Exception $e) {
            // @extensionScannerIgnoreLine
            $this->logger->error($e->getMessage());
        }
    }
}
