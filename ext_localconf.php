<?php

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

defined('TYPO3') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['LOG']['TWOH']['TwohTinyPng']['writerConfiguration'] = [
    // configuration for ERROR level log entries
    LogLevel::INFO => [
        // Add a FileWriter
        FileWriter::class => [
            // Configuration for the writer
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
    LogLevel::WARNING => [
        // Add a FileWriter
        FileWriter::class => [
            // Configuration for the writer
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
    LogLevel::ERROR => [
        // Add a FileWriter
        FileWriter::class => [
            // Configuration for the writer
            'logFile' => Environment::getVarPath() . '/log/typo3_tinypng_upload.log',
        ],
    ],
];