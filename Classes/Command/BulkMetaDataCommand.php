<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BulkMetaDataCommand
 */
class BulkMetaDataCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('A command that regenerate image meta data.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return GeneralUtility::makeInstance(
            MetaDataUtility::class,
        )->bulkMetaData();
    }
}
