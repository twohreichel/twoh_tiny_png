<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Command;

use Doctrine\DBAL\Driver\Exception;
use TWOH\TwohTinyPng\Domain\Service\BulkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BulkCompressionCommand
 * @package TWOH\TwohTinyPng\Command
 */
class BulkCompressionCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp('A command that compressed all images in fileadmin directory.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return GeneralUtility::makeInstance(
            BulkService::class
        )->bulkImages();
    }
}
